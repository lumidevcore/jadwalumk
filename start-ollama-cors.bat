@echo off
setlocal EnableExtensions EnableDelayedExpansion
TITLE Ollama CORS - jadwalteknik.wasmer.app

set "SITE_ORIGIN=https://jadwalteknik.wasmer.app"
set "OLLAMA_ORIGINS=%SITE_ORIGIN%"
set "OLLAMA_HOST=127.0.0.1:11434"
set "OLLAMA_CONTEXT_LENGTH=8192"

cls
echo ================================================
echo  Ollama Local API for jadwalteknik.wasmer.app
echo ================================================
echo.

echo [1/7] Menyetel CORS...
setx OLLAMA_ORIGINS "%SITE_ORIGIN%" >nul 2>&1

echo [2/7] Menutup Ollama App ^(GUI/tray^) agar tidak respawn...
taskkill /F /IM "ollama app.exe" /T >nul 2>&1

echo [3/7] Menghentikan ollama.exe lama...
taskkill /F /IM ollama.exe /T >nul 2>&1

echo [4/7] Menunggu proses background benar-benar berhenti...
timeout /t 2 /nobreak >nul

echo [5/7] Membebaskan port 11434...
for /L %%I in (1,1,10) do (
  set "FOUND_PID="
  for /f "usebackq delims=" %%P in (`powershell -NoProfile -Command "$p=(Get-NetTCPConnection -LocalPort 11434 -State Listen -ErrorAction SilentlyContinue ^| Select-Object -ExpandProperty OwningProcess -Unique); if($p){$p}"`) do (
    set "FOUND_PID=%%P"
    echo       Port dipakai PID %%P - dihentikan...
    taskkill /F /PID %%P /T >nul 2>&1
  )
  if not defined FOUND_PID goto :PORT_FREE
  timeout /t 1 /nobreak >nul
)

:PORT_CHECK
for /f "usebackq delims=" %%P in (`powershell -NoProfile -Command "$p=(Get-NetTCPConnection -LocalPort 11434 -State Listen -ErrorAction SilentlyContinue ^| Select-Object -ExpandProperty OwningProcess -Unique); if($p){$p}"`) do (
  echo ERROR: Port 11434 masih dipakai PID %%P.
  echo Tutup Ollama dari system tray, lalu jalankan BAT ini lagi.
  pause
  exit /b 1
)

:PORT_FREE
echo       Port 11434 bebas.

echo [6/7] Memastikan Ollama App tidak hidup kembali...
taskkill /F /IM "ollama app.exe" /T >nul 2>&1
timeout /t 1 /nobreak >nul

echo [7/7] Menjalankan Ollama manual dengan CORS Wasmer...
echo       HOST=%OLLAMA_HOST%
echo       ORIGIN=%OLLAMA_ORIGINS%
echo       CONTEXT=%OLLAMA_CONTEXT_LENGTH%
echo.
echo Jangan buka Ollama App GUI selama jendela ini hidup.
echo V5 FAST: text-layer diprioritaskan, vision dibatasi outputnya agar lebih cepat.
echo.
ollama serve

echo.
echo Ollama berhenti. Tekan tombol apa saja untuk menutup.
pause >nul
