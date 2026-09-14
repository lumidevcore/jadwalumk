# Jadwal Kuliah UMK — AI PDF to Schedule

Aplikasi web PHP untuk membaca PDF jadwal kuliah Universitas Muria Kudus (UMK), mengekstrak data jadwal menggunakan **Ollama lokal**, kemudian menyimpan dan menampilkan hasilnya sebagai halaman jadwal yang rapi, responsif, dan dapat diakses dari perangkat lain.

Project ini dibuat dengan pendekatan **local AI + web deployment**: model AI tetap berjalan di laptop pengguna melalui Ollama, sedangkan aplikasi web dapat di-deploy ke hosting seperti Wasmer.

> Status project: **development / personal project**. Struktur dan konfigurasi dapat berubah seiring pengembangan.

---

## Fitur Utama

- Upload file **PDF jadwal kuliah** melalui browser.
- Deteksi koneksi Ollama lokal otomatis melalui:
  - `http://localhost:11434`
  - `http://127.0.0.1:11434`
- Membaca daftar model yang terpasang dari Ollama.
- Model utama yang direkomendasikan:
  - `qwen3.5:4b`
- Model vision alternatif/fallback:
  - `qwen3-vl:4b`
- Mode proses cepat:
  - membaca **text layer PDF terlebih dahulu**;
  - vision hanya digunakan jika teks PDF tidak memadai.
- PDF.js dipakai untuk membaca PDF langsung dari browser.
- AI hanya diminta menghasilkan **JSON terstruktur**, bukan HTML penuh.
- HTML jadwal dibangun oleh aplikasi agar hasil lebih konsisten dan tidak mudah terpotong oleh batas context model.
- Tampilan jadwal responsif untuk desktop, tablet, dan mobile.
- Ringkasan jadwal mingguan.
- Penyimpanan data ke **Supabase**.
- Tabel `mahasiswa` berfungsi sebagai master data berdasarkan NIM.
- Riwayat hasil upload disimpan pada tabel `jadwal_upload`.
- Hanya jadwal lama milik mahasiswa yang sama yang dinonaktifkan ketika jadwal baru diunggah.
- Script Windows untuk menjalankan Ollama dengan konfigurasi CORS yang sesuai untuk web deployment.

---

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | PHP |
| Frontend | HTML, CSS, JavaScript |
| PDF Reader | PDF.js |
| Local AI | Ollama |
| Model utama | `qwen3.5:4b` |
| Vision fallback | `qwen3-vl:4b` |
| Database | Supabase / PostgreSQL |
| Font UI | Plus Jakarta Sans |
| Deployment web | Wasmer / server PHP yang kompatibel |

---

## Struktur Project

```text
jadwalumk/
├── api/
│   └── jadwal.php
│
├── config/
│   └── supabase.php
│
├── index.php
├── upload.php
├── start-ollama-cors.bat
├── OLLAMA-SETUP.txt
├── supabase.sql
└── README.md
```

### Penjelasan file

**`index.php`**  
Menampilkan jadwal aktif yang sudah disimpan di Supabase.

**`upload.php`**  
Halaman utama untuk:

- mengecek Ollama;
- membaca daftar model;
- upload PDF;
- ekstraksi text layer;
- fallback ke vision;
- meminta Ollama menghasilkan JSON jadwal;
- membuat HTML jadwal;
- mengirim hasil ke API untuk disimpan.

**`api/jadwal.php`**  
API PHP untuk penyimpanan data jadwal dan metadata mahasiswa ke Supabase.

**`config/supabase.php`**  
Konfigurasi koneksi Supabase dan helper REST API.

**`supabase.sql`**  
Schema database dan migration untuk tabel yang dibutuhkan aplikasi.

**`start-ollama-cors.bat`**  
Menjalankan Ollama secara manual di Windows dengan konfigurasi CORS yang sesuai dengan web deployment.

**`OLLAMA-SETUP.txt`**  
Catatan singkat setup Ollama.

---

# Cara Kerja Aplikasi

Alur utama aplikasi:

```text
PDF Jadwal
    │
    ▼
Browser / PDF.js
    │
    ├── Ada text layer ───────────────┐
    │                                 │
    │                                 ▼
    │                         Model text / multimodal
    │                         qwen3.5:4b
    │
    └── Text layer tidak cukup
              │
              ▼
       PDF → image di browser
              │
              ▼
       Vision model Ollama
       qwen3.5:4b / qwen3-vl:4b
              │
              ▼
          JSON Jadwal
              │
              ▼
      HTML dibuat oleh JavaScript
              │
              ▼
        API PHP → Supabase
              │
              ▼
            index.php
```

AI **tidak membuat halaman HTML secara langsung**. Model hanya membaca data jadwal dan mengembalikan JSON ringkas. Hal ini membuat output lebih stabil, lebih cepat, dan mengurangi risiko respons terpotong karena context limit.

---

# Prasyarat

## 1. PHP Hosting

Server harus mendukung PHP dan fungsi cURL.

Contoh target deployment:

- Wasmer
- Apache + PHP
- Nginx + PHP-FPM
- hosting PHP lainnya

## 2. Ollama di Windows

Install Ollama terlebih dahulu.

Cek:

```cmd
ollama --version
```

Cek model yang tersedia:

```cmd
ollama list
```

Contoh model yang digunakan project:

```text
qwen3.5:4b
qwen3-vl:4b
deepseek-r1:1.5b
```

Jika model belum ada:

```cmd
ollama pull qwen3.5:4b
```

Opsional untuk fallback vision:

```cmd
ollama pull qwen3-vl:4b
```

---

# Setup Supabase

## 1. Buat Project Supabase

Buat project baru dari dashboard Supabase.

Ambil:

- Project URL
- Anon / publishable key

## 2. Jalankan Schema

Buka:

```text
Supabase Dashboard → SQL Editor
```

Salin isi file:

```text
supabase.sql
```

kemudian jalankan.

Schema akan membuat tabel:

```text
settings
mahasiswa
jadwal_upload
```

### Tabel `mahasiswa`

Berfungsi sebagai master data mahasiswa.

Kolom utama:

```text
id
nama
nim
prodi
dosen_pa
sks
semester
dicetak
created_at
```

NIM dibuat unik sehingga satu mahasiswa tidak membuat row baru setiap upload.

### Tabel `jadwal_upload`

Menyimpan hasil jadwal yang pernah diunggah.

```text
id
mahasiswa_id
semester
html_content
is_active
uploaded_at
```

Ketika mahasiswa yang sama upload jadwal baru, jadwal lamanya akan dinonaktifkan dan jadwal terbaru menjadi aktif.

### Tabel `settings`

Dipakai sebagai cache / pointer cepat untuk data jadwal yang sedang ditampilkan.

---

# Konfigurasi Supabase

Buka:

```text
config/supabase.php
```

Isi:

```php
define('SUPABASE_URL', 'https://PROJECT_ID.supabase.co');
define('SUPABASE_KEY', 'YOUR_SUPABASE_ANON_KEY');
```

> Jangan masukkan `service_role` key ke repository publik. Jika repository GitHub bersifat Public, gunakan hanya key yang memang aman dipakai dari aplikasi publik dan atur RLS dengan benar.

---

# Menjalankan Ollama untuk Website

Website deployment berjalan dari domain HTTPS, sedangkan Ollama berjalan lokal di:

```text
http://127.0.0.1:11434
```

Browser membutuhkan konfigurasi CORS agar website dapat berkomunikasi dengan Ollama lokal.

Project sudah menyediakan:

```text
start-ollama-cors.bat
```

Jalankan file tersebut saat fitur AI akan dipakai.

Disarankan:

```text
Right click → Run as administrator
```

Script akan:

1. menyetel `OLLAMA_ORIGINS`;
2. menutup Ollama App GUI agar tidak melakukan respawn server;
3. menghentikan server Ollama lama;
4. memastikan port `11434` kosong;
5. menyetel context Ollama;
6. menjalankan `ollama serve` baru.

Konfigurasi utama:

```cmd
OLLAMA_HOST=127.0.0.1:11434
OLLAMA_CONTEXT_LENGTH=8192
```

Untuk deployment saat ini, origin CORS diarahkan ke:

```text
https://jadwalteknik.wasmer.app
```

Jika domain aplikasi berubah, edit:

```bat
set "SITE_ORIGIN=https://domain-baru.example"
```

pada `start-ollama-cors.bat`.

---

# Jangan Jalankan Dua Server Ollama

Jika muncul error:

```text
bind: Only one usage of each socket address ... is normally permitted
```

berarti port `11434` sudah digunakan proses Ollama lain.

Cek:

```cmd
netstat -ano | findstr :11434
```

Project BAT sudah mencoba menangani masalah ini secara otomatis.

Jika perlu secara manual:

```cmd
taskkill /F /IM "ollama app.exe" /T
taskkill /F /IM ollama.exe /T
```

lalu jalankan kembali:

```cmd
start-ollama-cors.bat
```

Jangan membuka Ollama App GUI lagi selama server manual sedang berjalan.

---

# Memastikan Ollama Aktif

Tes API:

```cmd
curl http://127.0.0.1:11434/api/tags
```

Jika berhasil akan muncul JSON daftar model.

Contoh:

```json
{
  "models": [
    {
      "name": "qwen3.5:4b"
    }
  ]
}
```

---

# Model AI

## Model Utama

```text
qwen3.5:4b
```

Model ini diprioritaskan karena pada pengujian project lebih stabil untuk membaca jadwal dan menghasilkan JSON terstruktur.

## Model Vision Alternatif

```text
qwen3-vl:4b
```

Digunakan sebagai fallback atau pilihan manual jika diperlukan.

## Model Lain

Model lain yang tersedia di Ollama tetap dapat terdeteksi dari endpoint:

```text
/api/tags
```

Tetapi tidak semua model cocok untuk vision atau ekstraksi jadwal.

---

# Mode Pemrosesan

## Otomatis Cepat

Mode yang direkomendasikan.

```text
PDF
 ↓
Coba text layer
 ↓
Jika cukup → AI text
Jika tidak → AI vision
```

Keuntungan:

- jauh lebih cepat untuk PDF digital;
- mengurangi penggunaan RAM;
- mengurangi image encoding;
- lebih ringan untuk CPU.

## Vision Akurat

Memaksa halaman PDF dikonversi menjadi gambar dan dikirim ke model vision.

Gunakan jika:

- PDF adalah hasil scan;
- text layer rusak;
- tabel tidak terbaca benar melalui mode text.

---

# Format Data AI

AI diminta mengembalikan JSON ringkas, misalnya:

```json
{
  "u": "UMK",
  "f": "Fakultas Teknik",
  "s": "Ganjil 2026/2027",
  "m": {
    "n": "Nama Mahasiswa",
    "i": "NIM",
    "p": "Teknik Informatika - S1",
    "d": "Nama Dosen PA",
    "t": "24",
    "c": "Tanggal cetak"
  },
  "r": [
    [
      "1",
      "C",
      "IFE307",
      "Embedded System",
      "Nama Dosen",
      "2",
      "",
      "",
      "",
      "",
      "09:40-11:19 J.4.06",
      "",
      ""
    ]
  ]
}
```

Setelah JSON diterima, JavaScript mengubahnya menjadi halaman HTML jadwal.

---

# Tampilan Jadwal

Halaman jadwal mencakup:

- identitas Universitas Muria Kudus;
- fakultas;
- semester;
- nama mahasiswa;
- NIM;
- program studi;
- dosen PA;
- total SKS;
- tabel mata kuliah;
- kelas;
- kode mata kuliah;
- dosen;
- SKS;
- jadwal Senin–Minggu;
- jam dan ruang;
- penanda mata kuliah praktikum;
- ringkasan jadwal mingguan;
- tombol upload jadwal baru.

Tampilan menggunakan layout responsif dengan warna navy/gold bertema UMK.

---

# Deployment

Setelah file project selesai:

```text
api/
config/
index.php
upload.php
start-ollama-cors.bat
OLLAMA-SETUP.txt
supabase.sql
README.md
```

upload ke hosting PHP.

Contoh URL aplikasi:

```text
https://jadwalteknik.wasmer.app/
```

Halaman upload:

```text
https://jadwalteknik.wasmer.app/upload.php
```

---

# Upload ke GitHub dari CMD

Masuk ke folder project:

```cmd
cd /d "%USERPROFILE%\OneDrive\Dokumen\GitHub\jadwalumk"
```

Pastikan file benar:

```cmd
dir
```

Inisialisasi repository:

```cmd
git init
git branch -M main
```

Tambahkan file:

```cmd
git add .
```

Commit:

```cmd
git commit -m "Initial clean upload"
```

Tambahkan remote:

```cmd
git remote add origin https://github.com/lumidevcore/jadwalumk.git
```

Push:

```cmd
git push -u origin main
```

Untuk update berikutnya:

```cmd
git add .
git commit -m "Update project"
git push
```

---

# Rekomendasi `.gitignore`

Buat file `.gitignore`:

```gitignore
.env
.env.*
vendor/
node_modules/
.vscode/
.idea/
*.log
Thumbs.db
.DS_Store
```

Jika nantinya konfigurasi Supabase dipindahkan ke environment variable, jangan commit file `.env`.

---

# Troubleshooting

## Ollama tidak terhubung

Tes:

```cmd
curl http://127.0.0.1:11434/api/tags
```

Jika tidak merespons, jalankan:

```cmd
start-ollama-cors.bat
```

---

## CORS Error

Contoh error browser:

```text
No 'Access-Control-Allow-Origin' header is present
```

Pastikan `OLLAMA_ORIGINS` sesuai domain deployment dan server Ollama sudah direstart setelah environment variable berubah.

---

## Port 11434 sudah digunakan

Cek PID:

```cmd
netstat -ano | findstr :11434
```

Cek proses:

```cmd
tasklist /FI "PID eq NOMOR_PID"
```

Biasanya proses yang berebut port adalah:

```text
ollama.exe
ollama app.exe
```

---

## AI sangat lambat

Pada perangkat tanpa dedicated GPU, Ollama dapat berjalan melalui CPU sehingga vision inference membutuhkan waktu cukup lama.

Gunakan mode:

```text
⚡ Otomatis Cepat
```

agar text layer PDF diprioritaskan.

---

## AMD iGPU tidak dipakai

Jika log menampilkan:

```text
dropping integrated GPU
```

atau:

```text
AMD driver is too old
```

Ollama kemungkinan melakukan inference dengan CPU.

Hal ini tidak membuat aplikasi gagal, tetapi proses vision akan lebih lambat.

---

## Model tidak muncul di dropdown

Pastikan model benar-benar ada:

```cmd
ollama list
```

Kemudian:

```cmd
curl http://127.0.0.1:11434/api/tags
```

Jika model muncul di API tetapi belum tampil di browser, lakukan hard refresh:

```text
Ctrl + Shift + R
```

---

# Catatan Keamanan

Project versi pengembangan ini menggunakan akses Supabase yang cukup terbuka agar integrasi mudah diuji.

Jika aplikasi akan dipakai publik, sangat disarankan untuk:

- memperketat Row Level Security (RLS);
- tidak memakai policy `FOR ALL USING (true)` untuk production;
- menggunakan autentikasi jika data mahasiswa bersifat privat;
- membatasi operasi insert/update hanya kepada user yang berwenang;
- tidak pernah menyimpan `service_role` key di source code atau GitHub;
- memindahkan konfigurasi sensitif ke environment variable.

> `anon key` Supabase memang dirancang untuk digunakan oleh client, tetapi keamanan sebenarnya tetap bergantung pada RLS dan policy database.

---

# Privasi

PDF jadwal diproses melalui browser dan Ollama yang berjalan di komputer lokal pengguna. Model AI tidak harus dikirim ke layanan AI cloud pihak ketiga.

Namun, hasil jadwal yang sudah diproses akan disimpan ke Supabase jika fitur penyimpanan aktif.

Pastikan deployment dan database dikonfigurasi sesuai kebutuhan privasi pengguna.

---

# Pengembangan Selanjutnya

Beberapa pengembangan yang dapat ditambahkan:

- login mahasiswa;
- memilih mahasiswa berdasarkan NIM;
- multi-user schedule dashboard;
- histori jadwal per semester;
- export jadwal ke kalender `.ics`;
- integrasi Google Calendar;
- reminder jadwal kuliah;
- notifikasi WhatsApp/email;
- CRUD mahasiswa dari dashboard admin;
- validasi bentrok jadwal;
- deteksi otomatis perubahan KRS;
- peningkatan dukungan GPU / iGPU untuk Ollama;
- fallback model yang lebih cerdas;
- cache hasil AI agar PDF yang sama tidak diproses dua kali.

---

# Disclaimer

Project ini adalah project independen untuk membantu pengelolaan dan visualisasi jadwal kuliah.

Nama **Universitas Muria Kudus (UMK)** digunakan sebagai konteks data dan tampilan aplikasi. Project ini bukan layanan resmi Universitas Muria Kudus kecuali dinyatakan secara resmi oleh institusi terkait.

---

## Author

**lumidevcore**

GitHub repository:

```text
https://github.com/lumidevcore/jadwalumk
```
