<?php // Halaman upload tidak membutuhkan koneksi DB langsung; penyimpanan lewat api/jadwal.php ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Upload Jadwal – UMK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<!-- PDF.js untuk konversi PDF → gambar di browser -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--navy:#0d1f3c;--gold:#d4a030;--bg:#f0f2f7;--white:#fff;--border:#e2e6f0;--text:#1a2540;--muted:#7a84a0;--green:#1e7a45;--red:#9c2c2c}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 16px}
.card{background:var(--white);border:1px solid var(--border);border-radius:24px;padding:40px;max-width:500px;width:100%;box-shadow:0 8px 40px rgba(13,31,60,.10);animation:up .4s ease both}
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.top{display:flex;align-items:center;gap:14px;margin-bottom:24px}
.logo{width:44px;height:44px;background:var(--navy);border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:var(--gold);flex-shrink:0}
.top h1{font-size:16px;font-weight:800;color:var(--navy)}
.top p{font-size:11.5px;color:var(--muted);margin-top:2px}
.back{margin-left:auto;font-size:12px;font-weight:600;color:var(--muted);text-decoration:none}

/* Ollama status */
.ol-status{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:12px;border:1px solid var(--border);background:var(--bg);margin-bottom:18px;font-size:12px;font-weight:600}
.dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;background:#ccc}
.dot.ok{background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.2)}
.dot.err{background:var(--red)}
.dot.checking{background:var(--gold);animation:pulse 1s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.ol-detail{font-size:11px;color:var(--muted);font-weight:400;margin-top:1px;word-break:break-word}
.ol-actions{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap}
.ol-mini{border:1px solid var(--border);background:#fff;color:var(--navy);border-radius:8px;padding:6px 9px;font:700 11px 'Plus Jakarta Sans',sans-serif;cursor:pointer}
.ol-mini:hover{border-color:var(--gold)}
.diag{display:none;margin:-8px 0 16px;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fafbfe;font-size:11px;color:var(--muted);line-height:1.6}
.diag.show{display:block}
.diag code{font-size:10.5px;background:#eef1f7;padding:2px 4px;border-radius:4px;word-break:break-all}

/* Model selector */
.field{margin-bottom:14px}
.lbl{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;display:block}
select{width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:var(--text);background:var(--bg);outline:none;cursor:pointer}
select:focus{border-color:var(--gold)}

hr{border:none;border-top:1px solid var(--border);margin:18px 0}

/* Drop zone */
.dz{border:2px dashed var(--border);border-radius:14px;padding:36px 20px;text-align:center;cursor:pointer;background:var(--bg);transition:.2s;position:relative}
.dz:hover,.dz.drag{border-color:var(--gold);background:#fdf8ee}
.dz input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.dz-ic{width:48px;height:48px;background:var(--navy);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px}
.dz h3{font-size:14px;font-weight:700;margin-bottom:3px}
.dz p{font-size:12px;color:var(--muted)}
.dz p span{color:var(--navy);font-weight:600}
.sf{display:none;align-items:center;gap:10px;background:#eef3ff;border:1px solid #c0d0ff;border-radius:10px;padding:11px 13px;margin-top:10px}
.sf.show{display:flex}
.sf-ic{width:30px;height:30px;background:var(--navy);border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sf-name{font-size:13px;font-weight:700}
.sf-size{font-size:11px;color:var(--muted);margin-top:1px}

/* Hint */
.hint{background:#fff8e6;border:1px solid #f5c842;border-radius:10px;padding:11px 13px;font-size:12px;color:#7a5800;line-height:1.7;margin-bottom:16px}
.hint strong{display:block;margin-bottom:3px}
.hint code{background:rgba(0,0,0,.07);padding:1px 5px;border-radius:4px;font-size:11px}

.btn{width:100%;padding:15px;border-radius:12px;border:none;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:700;background:var(--navy);color:#fff;margin-top:14px;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s}
.btn:hover:not(:disabled){background:#1a3260;transform:translateY(-1px)}
.btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
.btn.success{background:var(--green)}
.sb{display:none;margin-top:12px;border-radius:10px;padding:12px 14px;font-size:13px;line-height:1.6}
.sb.show{display:block}
.sb.error{background:#fff0f0;border:1px solid #ffb0b0;color:#8b0000}
.sb.ok{background:#edfaf3;border:1px solid #a0e0c0;color:#0d5c32}

/* Overlay loading */
#ov{display:none;position:fixed;inset:0;background:rgba(13,31,60,.78);z-index:999;align-items:center;justify-content:center;flex-direction:column;gap:16px}
#ov.show{display:flex}
.spin{width:48px;height:48px;border:4px solid rgba(212,160,48,.25);border-top-color:#d4a030;border-radius:50%;animation:sp 1s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}
.ov-title{color:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-size:15px;font-weight:700}
.ov-step{color:rgba(255,255,255,.55);font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;text-align:center;max-width:300px;line-height:1.6}
.ov-step b{color:#d4a030}
</style>
</head>
<body>

<div id="ov">
  <div class="spin"></div>
  <div class="ov-title">Memproses...</div>
  <div class="ov-step" id="ovStep">Menyiapkan PDF...</div>
</div>

<div class="card">
  <div class="top">
    <div class="logo">UMK</div>
    <div>
      <h1>Upload Jadwal</h1>
      <p>Universitas Muria Kudus <span style="margin-left:6px;color:#d4a030;font-weight:800">v6.4</span></p>
    </div>
    <a href="index.php" class="back">← Kembali</a>
  </div>

  <!-- STATUS OLLAMA -->
  <div class="ol-status" id="olStatus">
    <div class="dot checking" id="olDot"></div>
    <div style="min-width:0;flex:1">
      <div id="olMsg">Mengecek koneksi Ollama lokal...</div>
      <div class="ol-detail" id="olDetail">localhost:11434</div>
      <div class="ol-actions">
        <button type="button" class="ol-mini" onclick="checkOllama(true)">↻ Cek Ulang</button>
        <button type="button" class="ol-mini" onclick="toggleDiag()">Detail Diagnostik</button>
      </div>
    </div>
  </div>
  <div class="diag" id="diagBox"></div>

  <!-- HINT CORS -->
  <div class="hint" id="corsHint" style="display:none">
    <strong>⚠ Ollama hidup, tetapi browser kemungkinan memblokir CORS</strong>
    Situs ini berjalan di <code>https://jadwalteknik.wasmer.app</code> dan perlu izin dari Ollama lokal.<br>
    Buka CMD sebagai Administrator, matikan Ollama lama, lalu jalankan:<br>
    <code>taskkill /F /IM ollama.exe /T</code><br>
    <code>set OLLAMA_ORIGINS=https://jadwalteknik.wasmer.app</code><br>
    <code>ollama serve</code><br>
    Setelah itu biarkan CMD Ollama tetap terbuka dan klik <b>Cek Ulang</b>.
  </div>

  <!-- PILIH MODEL -->
  <div class="field">
    <label class="lbl">Model Vision (fallback jika PDF tidak punya teks)</label>
    <select id="modelSel">
      <option value="qwen3.5:4b">qwen3.5:4b ✓ (utama / paling stabil)</option>
    </select>
  </div>

  <div class="field">
    <label class="lbl">Mode Proses</label>
    <select id="speedMode">
      <option value="auto" selected>⚡ Otomatis Cepat — baca teks PDF dulu, vision hanya jika perlu</option>
      <option value="vision">👁 Vision Akurat — selalu kirim gambar ke model</option>
    </select>
  </div>

  <hr>

  <!-- DROP ZONE PDF -->
  <div class="dz" id="dz" ondragover="ev(event,1)" ondragleave="ev(event,0)" ondrop="drop(event)">
    <input type="file" id="fi" accept=".pdf" onchange="pick(this)"/>
    <div class="dz-ic">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
    </div>
    <h3>Seret PDF jadwal ke sini</h3>
    <p>atau <span>klik untuk pilih file</span></p>
  </div>
  <div class="sf" id="sf">
    <div class="sf-ic">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div>
      <div class="sf-name" id="sfN">–</div>
      <div class="sf-size" id="sfS">–</div>
    </div>
  </div>

  <div class="sb" id="sb"></div>

  <button class="btn" id="btn" disabled onclick="run()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    Generate &amp; Simpan Jadwal
  </button>
</div>

<script>
// PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

const OLLAMA_ENDPOINTS = [
  'http://localhost:11434',
  'http://127.0.0.1:11434'
];
let OLLAMA = OLLAMA_ENDPOINTS[0];
let selFile = null, ollamaOk = false;
let lastDiag = [];
let installedModels = [];
let visionModels = [];


function esc(s='') {
  return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
}
function toggleDiag() {
  const box = document.getElementById('diagBox');
  box.classList.toggle('show');
}
function renderDiag() {
  const box = document.getElementById('diagBox');
  if (!lastDiag.length) {
    box.innerHTML = 'Belum ada hasil diagnostik.';
    return;
  }
  box.innerHTML = lastDiag.map(x => `<div>• <code>${esc(x.url)}</code> — ${esc(x.result)}</div>`).join('');
}

function classifyFetchError(err) {
  const text = String(err?.message || err || 'Failed to fetch');
  if (err?.httpStatus === 403 || /HTTP\s*403/i.test(text)) return 'cors_403';
  if (err?.httpStatus) return 'http_' + err.httpStatus;
  if (/abort|timeout/i.test(text)) return 'timeout';
  // Browser sengaja menyembunyikan detail CORS/PNA dan biasanya hanya memberi TypeError/Failed to fetch.
  if (/failed to fetch|load failed|networkerror|typeerror/i.test(text)) return 'cors_or_browser_block';
  return 'network_error';
}

// ── Cek koneksi Ollama ──
async function checkOllama(manual=false) {
  ollamaOk = false;
  updateBtn();
  document.getElementById('olDot').className = 'dot checking';
  document.getElementById('olMsg').textContent = manual ? 'Mengecek ulang Ollama...' : 'Mengecek koneksi Ollama lokal...';
  document.getElementById('olDetail').textContent = 'Mencoba localhost dan 127.0.0.1...';
  document.getElementById('corsHint').style.display = 'none';
  lastDiag = [];

  let lastKind = 'network_error';
  for (const base of OLLAMA_ENDPOINTS) {
    try {
      const controller = new AbortController();
      const timer = setTimeout(() => controller.abort(), 5000);
      const r = await fetch(base + '/api/tags', {
        method: 'GET',
        mode: 'cors',
        cache: 'no-store',
        signal: controller.signal
      });
      clearTimeout(timer);
      if (!r.ok) {
        const body = await r.text().catch(() => '');
        const err = new Error('HTTP ' + r.status + (body ? ' — ' + body.slice(0, 120) : ''));
        err.httpStatus = r.status;
        throw err;
      }
      const d = await r.json();
      const rawModels = Array.isArray(d.models) ? d.models : [];
      installedModels = rawModels;
      const models = rawModels.map(m => m.name || m.model).filter(Boolean);

      OLLAMA = base;
      const sel = document.getElementById('modelSel');
      sel.innerHTML = '';

      const isVision = m => {
        const caps = Array.isArray(m.capabilities) ? m.capabilities : [];
        const name = String(m.name || m.model || '').toLowerCase();
        return caps.includes('vision') || name.includes('vl') || name.includes('vision') || name.includes('llava');
      };
      // Paksa dua model vision yang memang ada di mesin ini ikut tampil.
      // Ini menghindari kasus metadata capability dari Ollama tidak konsisten.
      const visionRaw = rawModels.filter(m => {
        const name = String(m.name || m.model || '');
        return isVision(m) || name === 'qwen3.5:4b' || name === 'qwen3-vl:4b';
      });
      visionModels = visionRaw;
      const preferredVision = ['qwen3.5:4b','qwen3-vl:4b'];
      const orderedVision = [
        ...preferredVision.map(n => visionRaw.find(m => (m.name || m.model) === n)).filter(Boolean),
        ...visionRaw.filter(m => !preferredVision.includes(m.name || m.model))
      ];
      // Dropdown ini khusus model yang bisa baca gambar.
      const ordered = orderedVision;
      const seen = new Set();
      ordered.forEach(m => {
        const name = m.name || m.model;
        if (!name || seen.has(name)) return;
        seen.add(name);
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name + (name === 'qwen3.5:4b' ? ' ✓ UTAMA / rekomendasi' : ' ✓ Vision');
        sel.appendChild(opt);
      });

      // Paksa model yang terbukti stabil di perangkat ini sebagai default.
      if ([...sel.options].some(o => o.value === 'qwen3.5:4b')) {
        sel.value = 'qwen3.5:4b';
      }

      lastDiag.push({url: base + '/api/tags', result: `OK (${models.length} model)`});
      renderDiag();
      const visionCount = visionRaw.length;
      setOlStatus(true,
        `Ollama terhubung — ${models.length} model tersedia`,
        `${base} • vision: ${orderedVision.map(m => m.name || m.model).join(', ') || 'tidak ada'}`);
      ollamaOk = true;
      document.getElementById('corsHint').style.display = 'none';
      updateBtn();
      return;
    } catch(e) {
      const kind = classifyFetchError(e);
      lastKind = kind;
      lastDiag.push({url: base + '/api/tags', result: `${kind}: ${e?.message || e}`});
      renderDiag();
    }
  }

  if (lastKind === 'cors_403') {
    setOlStatus(false,
      'Ollama aktif, tetapi domain Wasmer ditolak (HTTP 403)',
      'Kemungkinan instance Ollama lama masih memakai port 11434. Jalankan start-ollama-cors.bat v3.');
    document.getElementById('corsHint').style.display = 'block';
  } else if (lastKind === 'cors_or_browser_block') {
    setOlStatus(false,
      'Browser memblokir koneksi ke Ollama',
      'Ollama bisa saja hidup, tetapi izin CORS untuk domain Wasmer belum aktif');
    document.getElementById('corsHint').style.display = 'block';
  } else if (lastKind === 'timeout') {
    setOlStatus(false,
      'Ollama tidak merespons',
      'Timeout saat menghubungi localhost:11434');
  } else {
    setOlStatus(false,
      'Ollama tidak terhubung',
      'Pastikan Ollama berjalan di port 11434 lalu klik Cek Ulang');
  }
}

function setOlStatus(ok, msg, detail) {
  document.getElementById('olDot').className = 'dot ' + (ok ? 'ok' : 'err');
  document.getElementById('olMsg').textContent = msg;
  document.getElementById('olDetail').textContent = detail || '';
}

// ── File handling ──
function ev(e,on){e.preventDefault();document.getElementById('dz').classList.toggle('drag',!!on);}
function drop(e){e.preventDefault();document.getElementById('dz').classList.remove('drag');const f=e.dataTransfer.files[0];if(f?.type==='application/pdf')setF(f);else showSb('error','Harap file PDF.');}
function pick(i){if(i.files[0])setF(i.files[0]);}
function setF(f){selFile=f;document.getElementById('sfN').textContent=f.name;document.getElementById('sfS').textContent=(f.size/1024).toFixed(1)+' KB';document.getElementById('sf').classList.add('show');clearSb();updateBtn();}
function updateBtn(){document.getElementById('btn').disabled=!(selFile&&ollamaOk);}
function showSb(t,m){const e=document.getElementById('sb');e.className='sb show '+t;e.innerHTML=m;}
function clearSb(){document.getElementById('sb').className='sb';}
function setStep(msg){document.getElementById('ovStep').innerHTML=msg;}

// ── PDF text + gambar teroptimasi ──
async function loadPdf(file) {
  const ab = await file.arrayBuffer();
  return await pdfjsLib.getDocument({data: ab}).promise;
}

async function pdfToText(file) {
  const pdf = await loadPdf(file);
  const pages = [];
  const stats = [];
  for (let i = 1; i <= pdf.numPages; i++) {
    const page = await pdf.getPage(i);
    const tc = await page.getTextContent();
    const items = (tc.items || []).map(it => ({
      text: String(it.str || '').trim(),
      x: Number(it.transform?.[4] || 0),
      y: Number(it.transform?.[5] || 0)
    })).filter(x => x.text);
    items.sort((a,b) => Math.abs(b.y-a.y) > 2 ? b.y-a.y : a.x-b.x);
    let out = [], line = [], lastY = null;
    for (const it of items) {
      if (lastY !== null && Math.abs(it.y-lastY) > 2) {
        if (line.length) out.push(line.join(' | '));
        line = [];
      }
      line.push(it.text);
      lastY = it.y;
    }
    if (line.length) out.push(line.join(' | '));
    const text = out.join('\n').trim();
    pages.push(`--- HALAMAN ${i} ---\n${text}`);
    stats.push({page:i, chars:text.length, items:items.length, text});
  }
  return { text: pages.join('\n\n').trim(), stats, numPages: pdf.numPages };
}

function textLooksLikeSchedule(text='') {
  const t = String(text).replace(/\s+/g,' ').trim();
  if (!t) return false;
  const keywords = ['senin','selasa','rabu','kamis','jumat',"jum\'at",'sks','mata kuliah','kode','kelas','dosen','ruang','jam'];
  const hits = keywords.reduce((n,k) => n + (t.toLowerCase().includes(k) ? 1 : 0), 0);
  // V4 memakai batas 250 karakter dan terlalu mudah salah fallback ke vision.
  // V5 menerima PDF text-layer yang pendek jika sinyal jadwal cukup kuat.
  return (t.length >= 80 && hits >= 2) || t.length >= 180;
}

async function pdfToImages(file, fast=true, maxPages=2) {
  const pdf = await loadPdf(file);
  const images = [];
  const count = fast ? Math.min(pdf.numPages, maxPages) : pdf.numPages;
  for (let i = 1; i <= count; i++) {
    const page = await pdf.getPage(i);
    // V5 FAST: resolusi lebih rendah + JPEG lebih kecil. Teks masih cukup tajam untuk dokumen jadwal.
    const scale = fast ? 1.45 : 1.75;
    const vp = page.getViewport({scale});
    const canvas = document.createElement('canvas');
    canvas.width = Math.ceil(vp.width);
    canvas.height = Math.ceil(vp.height);
    const ctx = canvas.getContext('2d', {alpha:false});
    ctx.fillStyle = '#fff';
    ctx.fillRect(0,0,canvas.width,canvas.height);
    await page.render({canvasContext: ctx, viewport: vp}).promise;
    const quality = fast ? 0.86 : 0.92;
    images.push(canvas.toDataURL('image/jpeg', quality).split(',')[1]);
  }
  return {images, totalPages:pdf.numPages, sentPages:count};
}

function modelParamBillions(m) {
  const txt = String(m?.details?.parameter_size || '').toUpperCase();
  const mm = txt.match(/([0-9.]+)\s*B/);
  return mm ? parseFloat(mm[1]) : 999;
}

function pickFastTextModel() {
  const candidates = installedModels.filter(m => {
    const name = String(m.name || m.model || '');
    return !!name;
  });
  if (!candidates.length) return document.getElementById('modelSel').value;

  const preferred = [
    'qwen3.5:4b',
    'kwangsuklee/Qwen3.5-4B.Q4_K_M-Claude-4.6-Opus-Reasoning-Distilled-v2:latest',
    'deepseek-r1:1.5b'
  ];
  for (const want of preferred) {
    const hit = candidates.find(m => (m.name || m.model) === want);
    if (hit) return hit.name || hit.model;
  }

  const nonVision = candidates.filter(m => {
    const caps = Array.isArray(m.capabilities) ? m.capabilities : [];
    const name = String(m.name || m.model || '').toLowerCase();
    return !caps.includes('vision') && !name.includes('vl') && !name.includes('vision') && !name.includes('llava');
  });
  if (!nonVision.length) return document.getElementById('modelSel').value;
  nonVision.sort((a,b) => modelParamBillions(a) - modelParamBillions(b));
  return nonVision[0].name || nonVision[0].model;
}


function h(v='') {
  return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
}

function buildScheduleHtml(d) {
  const m = d.mahasiswa || {};
  const rows = Array.isArray(d.mata_kuliah) ? d.mata_kuliah : [];
  const days = [
    ['sn','Senin'],['sl','Selasa'],['rb','Rabu'],['km','Kamis'],['jm','Jumat'],['sb','Sabtu'],['mg','Minggu']
  ];
  const shortDays = {sn:'Sn', sl:'Sl', rb:'Rb', km:'Km', jm:'Jm', sb:'Sb', mg:'Mg'};
  const palette = ['--c0','--c1','--c2','--c3','--c4','--c5','--c6','--c7','--c8'];

  function slotParts(raw) {
    raw = String(raw || '').replace(/\r/g, '').trim();
    if (!raw) return {time:'', room:''};
    if (raw.includes('\n')) {
      const lines = raw.split('\n').map(s => s.trim()).filter(Boolean);
      return {time: lines[0] || '', room: lines.slice(1).join(' ')};
    }
    const m1 = raw.match(/^([0-9]{1,2}[:.][0-9]{2}\s*[\-–]\s*[0-9]{1,2}[:.][0-9]{2})\s*[\(\[]?([^\)\]]+)?[\)\]]?$/);
    if (m1) return {time: m1[1].trim(), room: (m1[2] || '').trim()};
    const m2 = raw.match(/^([0-9]{1,2}[:.][0-9]{2}\s*[\-–]\s*[0-9]{2}[:.][0-9]{2})(.+)$/);
    if (m2) return {time: m2[1].trim(), room: m2[2].trim()};
    return {time: raw, room: ''};
  }

  function tdSlot(val, colorVar) {
    const raw = String(val || '').trim();
    if (!raw) return '<td class="emp">·</td>';
    const p = slotParts(raw);
    return `<td class="dtd"><div class="pill" style="background:var(${colorVar})"><div class="pt">${h(p.time || raw)}</div>${p.room ? `<div class="pr">${h(p.room)}</div>` : ''}</div></td>`;
  }

  const tableRows = rows.map((r, i) => {
    const colorVar = palette[i % palette.length];
    const praktikum = /praktikum/i.test(String(r.nama_mk || '')) ? '<span class="prak-tag">PRAKTIKUM</span>' : '';
    const dayCells = days.map(([k]) => tdSlot(r[k], colorVar)).join('');
    return `<tr>
      <td class="tc num">${h(r.no || i + 1)}</td>
      <td class="tc"><span class="kls-badge">${h(r.kelas)}</span></td>
      <td><span class="kode-tag">${h(r.kode_mk)}</span></td>
      <td><div class="mk-name">${h(r.nama_mk)}</div>${praktikum}</td>
      <td><div class="dosen">${h(r.dosen || '–')}</div></td>
      <td><span class="sks-num">${h(r.sks)}</span></td>
      ${dayCells}
    </tr>`;
  }).join('');

  const weekly = days.slice(0, 6).map(([k, label]) => {
    const items = rows
      .map((r, i) => ({r, i, val: String(r[k] || '').trim()}))
      .filter(x => x.val)
      .map(x => {
        const colorVar = palette[x.i % palette.length];
        const p = slotParts(x.val);
        return `<div class="wcard" style="background:var(${colorVar})">
          <div class="wt">${h(p.time || x.val)}</div>
          <div class="wn">${h(x.r.nama_mk)}</div>
          <div class="wr">${h(x.r.kode_mk)}${p.room ? ' · ' + h(p.room) : ''}</div>
        </div>`;
      }).join('');
    return `<div class="dcol"><div class="dhead">${label}</div><div class="dbody">${items || '<div class="no-cls">Tidak ada jadwal</div>'}</div></div>`;
  }).join('');

  const totalSks = m.total_sks || rows.reduce((n, r) => n + (parseInt(r.sks, 10) || 0), 0);
  const mkCount = rows.length;

  return `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jadwal Kuliah – ${h(d.semester || 'UMK')}</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --navy: #0d1f3c; --gold: #d4a030; --bg: #f0f2f7;
  --white: #ffffff; --border: #e2e6f0; --text: #1a2540; --muted: #7a84a0;
  --c0: #2563a8; --c1: #16736b; --c2: #7c3d9e; --c3: #b85c00;
  --c4: #1e7a45; --c5: #9c2c2c; --c6: #3d5fa8; --c7: #5a4a9e; --c8: #0d6b7a;
}
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); font-size: 14px; padding: 32px 24px 60px; }
.wrap { max-width: 1120px; margin: 0 auto; }
.header { background: var(--navy); border-radius: 18px; padding: 28px 34px; display: flex; align-items: center; gap: 20px; margin-bottom: 16px; position: relative; overflow: hidden; }
.header::after { content: ''; position: absolute; right: -40px; top: -40px; width: 220px; height: 220px; border-radius: 50%; background: radial-gradient(circle, rgba(212,160,48,.1) 0%, transparent 65%); pointer-events: none; }
.h-logo { width: 50px; height: 50px; background: var(--gold); border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; color: var(--navy); flex-shrink: 0; }
.h-info h1 { font-size: 15px; font-weight: 700; color: #fff; line-height: 1.35; }
.h-info p { font-size: 11px; font-weight: 600; color: rgba(212,160,48,.8); margin-top: 3px; text-transform: uppercase; letter-spacing: .08em; }
.h-right { margin-left: auto; text-align: right; }
.h-right .sem-lbl { font-size: 10px; font-weight: 600; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .1em; }
.h-right .sem-val { font-size: 15px; font-weight: 700; color: var(--gold); margin-top: 2px; }
.upload-btn { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; text-decoration: none; font-size: 11px; font-weight: 600; padding: 7px 14px; border-radius: 8px; margin-top: 9px; transition: background .2s; }
.upload-btn:hover { background: rgba(255,255,255,.18); }
.info-grid { display: grid; grid-template-columns: 2fr 1fr 1.5fr 1.5fr .7fr; gap: 10px; margin-bottom: 26px; }
.icard { background: var(--white); border: 1px solid var(--border); border-radius: 13px; padding: 14px 17px; }
.lbl { font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 5px; }
.val { font-size: 13px; font-weight: 700; color: var(--text); line-height: 1.3; }
.icard.sks-card { background: var(--navy); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
.sks-card .lbl { color: rgba(255,255,255,.4); }
.sks-card .val { font-size: 26px; font-weight: 800; color: var(--gold); }
.sec-h { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.sec-h h2 { font-size: 13px; font-weight: 700; white-space: nowrap; }
.sec-line { flex: 1; height: 1px; background: var(--border); }
.sec-badge { background: var(--navy); color: #fff; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.tbl-wrap { overflow-x: auto; margin-bottom: 28px; border-radius: 16px; box-shadow: 0 1px 18px rgba(13,31,60,.08); -webkit-overflow-scrolling: touch; }
table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: 16px; overflow: hidden; min-width: 860px; }
thead tr.r1 { background: var(--navy); }
thead th { color: #fff; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: .07em; white-space: nowrap; padding: 13px 13px; text-align: left; border-right: 1px solid rgba(255,255,255,.06); }
thead th.tc { text-align: center; }
thead th.day-th { background: #0f2a56; color: rgba(212,160,48,.85); font-size: 9.5px; text-align: center; padding: 13px 5px; }
thead th.sub { background: #14264e; font-size: 9.5px; color: rgba(255,255,255,.4); font-weight: 600; padding: 7px 13px; }
tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f5f7fd; }
tbody td { padding: 10px 13px; vertical-align: middle; border-right: 1px solid var(--border); font-size: 13px; }
tbody td:last-child { border-right: none; }
tbody td.tc { text-align: center; }
.num { color: var(--muted); font-weight: 700; font-size: 12px; }
.kls-badge { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 7px; background: var(--navy); color: #fff; font-size: 12px; font-weight: 700; }
.kode-tag { font-size: 10px; font-weight: 700; color: var(--muted); background: var(--bg); border: 1px solid var(--border); padding: 2px 6px; border-radius: 5px; display: inline-block; margin-bottom: 2px; letter-spacing: .03em; }
.mk-name { font-size: 13px; font-weight: 600; line-height: 1.3; }
.prak-tag { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #16736b; background: #e6f5f3; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 3px; }
.dosen { font-size: 12px; font-weight: 500; color: #3a4a6a; line-height: 1.4; }
.sks-num { font-size: 17px; font-weight: 800; color: var(--gold); display: block; text-align: center; }
.pill { display: inline-block; border-radius: 8px; padding: 6px 8px; text-align: center; min-width: 76px; }
.pill .pt { font-size: 11px; font-weight: 700; color: #fff; line-height: 1.25; }
.pill .pr { font-size: 9.5px; color: rgba(255,255,255,.72); margin-top: 2px; }
td.dtd { text-align: center; padding: 7px 5px; }
td.emp { text-align: center; color: #d0d5e5; font-size: 14px; }
.wgrid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-bottom: 30px; }
.dcol { background: var(--white); border: 1px solid var(--border); border-radius: 13px; overflow: hidden; }
.dhead { background: var(--navy); color: #fff; text-align: center; font-size: 10.5px; font-weight: 700; padding: 10px 6px; text-transform: uppercase; letter-spacing: .06em; }
.dbody { padding: 8px; display: flex; flex-direction: column; gap: 6px; min-height: 50px; }
.wcard { border-radius: 9px; padding: 9px 10px; }
.wt { font-size: 9px; font-weight: 600; color: rgba(255,255,255,.65); margin-bottom: 2px; }
.wn { font-size: 11px; font-weight: 700; color: #fff; line-height: 1.3; }
.wr { font-size: 9px; color: rgba(255,255,255,.6); margin-top: 3px; }
.no-cls { color: var(--muted); font-size: 11px; text-align: center; padding: 16px 0; font-style: italic; }
.footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding-top: 20px; border-top: 1px solid var(--border); font-size: 12px; color: var(--muted); }
.footer strong { color: var(--text); }
.footer .fr { text-align: right; }
.footer .fr strong { display: block; font-size: 13px; font-weight: 700; }
@media(max-width: 980px) {
  body { padding: 18px 14px 36px; }
  .info-grid { grid-template-columns: 1fr 1fr; }
  .sks-card { grid-column: span 2; }
  .wgrid { grid-template-columns: repeat(3, 1fr); }
  .header { flex-wrap: wrap; padding: 22px; }
  .h-right { margin-left: 0; text-align: left; width: 100%; }
}
@media(max-width: 760px) {
  .sec-h { flex-wrap: wrap; }
  .sec-line { display: none; }
  .tbl-wrap { border-radius: 14px; }
}
@media(max-width: 560px) {
  body { padding: 12px 10px 24px; }
  .header { gap: 14px; padding: 18px 16px; }
  .h-info h1 { font-size: 13px; }
  .h-info p { font-size: 10px; }
  .info-grid { grid-template-columns: 1fr; }
  .sks-card { grid-column: auto; }
  .wgrid { grid-template-columns: 1fr; }
  .icard { padding: 13px 14px; }
  .footer, .footer .fr { text-align: left; }
}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="h-logo">UMK</div>
    <div class="h-info">
      <h1>${h(d.universitas || 'Universitas Muria Kudus')} — ${h(d.fakultas || 'Fakultas Teknik')}</h1>
      <p>Jadwal Kuliah &nbsp;·&nbsp; ${h(m.prodi || 'Teknik Informatika – S1')}</p>
    </div>
    <div class="h-right">
      <div class="sem-lbl">Semester</div>
      <div class="sem-val">${h(d.semester || '')}</div>
      <a href="upload.php" class="upload-btn">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload Jadwal Baru
      </a>
    </div>
  </div>

  <div class="info-grid">
    <div class="icard"><div class="lbl">Nama</div><div class="val">${h(m.nama)}</div></div>
    <div class="icard"><div class="lbl">NIM</div><div class="val">${h(m.nim)}</div></div>
    <div class="icard"><div class="lbl">Program Studi</div><div class="val">${h(m.prodi)}</div></div>
    <div class="icard"><div class="lbl">Dosen PA</div><div class="val">${h(m.dosen_pa)}</div></div>
    <div class="icard sks-card"><div class="lbl">SKS</div><div class="val">${h(totalSks)}</div></div>
  </div>

  <div class="sec-h"><h2>Tabel Jadwal</h2><div class="sec-line"></div><span class="sec-badge">${mkCount} Matakuliah</span></div>
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr class="r1">
          <th class="tc" style="width:40px" rowspan="2">No.</th>
          <th class="tc" style="width:44px" rowspan="2">Kls</th>
          <th colspan="2">Matakuliah</th>
          <th rowspan="2">Dosen</th>
          <th class="tc" style="width:44px" rowspan="2">SKS</th>
          <th colspan="7" class="tc">Jadwal</th>
        </tr>
        <tr class="r1">
          <th class="sub" style="width:72px">Kode</th>
          <th class="sub">Nama</th>
          ${days.map(([k]) => `<th class="day-th" style="width:88px">${shortDays[k]}</th>`).join('')}
        </tr>
      </thead>
      <tbody>${tableRows}</tbody>
    </table>
  </div>

  <div class="sec-h"><h2>Ringkasan Mingguan</h2><div class="sec-line"></div></div>
  <div class="wgrid">${weekly}</div>

  <div class="footer">
    <div>Dicetak: <strong>${h(m.tanggal_cetak || '')}</strong> &nbsp;·&nbsp; Sumber: Kanal UMK</div>
    <div class="fr"><strong>${h(m.dosen_pa || '')}</strong>Dosen PA</div>
  </div>
</div>
</body>
</html>`;
}

// ── Main process ──
async function run() {
  if (!selFile || !ollamaOk) return;
  const btn = document.getElementById('btn');
  btn.disabled = true; clearSb();
  document.getElementById('ov').classList.add('show');

  try {
    // 1. Mode otomatis: ambil teks PDF dulu. Ini biasanya 3–10x lebih cepat daripada vision CPU.
    const speedMode = document.getElementById('speedMode').value;
    setStep('Membaca text-layer PDF...');
    const textResult = await pdfToText(selFile).catch(() => ({text:'',stats:[],numPages:0}));
    const pdfText = textResult.text || '';
    const usableText = textLooksLikeSchedule(pdfText);
    const useText = speedMode === 'auto' && usableText;

    let model, images = [];
    let visionMeta = null;
    if (useText) {
      model = pickFastTextModel();
      const chars = pdfText.replace(/\s+/g,' ').trim().length;
      setStep(`⚡ Text-layer terdeteksi (${chars} karakter).<br>Memakai <b>${model}</b> tanpa vision.`);
    } else {
      model = document.getElementById('modelSel').value;
      setStep('Text-layer tidak cukup — membuat gambar FAST...');
      visionMeta = await pdfToImages(selFile, speedMode === 'auto', 2);
      images = visionMeta.images;
      const note = visionMeta.totalPages > visionMeta.sentPages ? `<br><small>FAST: ${visionMeta.sentPages}/${visionMeta.totalPages} halaman pertama dikirim.</small>` : '';
      setStep(`👁 Mengirim ${images.length} halaman ke <b>${model}</b>...${note}<br><small>Output dibatasi agar tidak lagi 7–8 menit.</small>`);
    }

    // Format JSON ringkas mengurangi token output secara drastis.
    const PROMPT = `Baca jadwal kuliah UMK dari dokumen ini. Balas HANYA 1 JSON valid, tanpa markdown, tanpa penjelasan, tanpa reasoning.

Schema WAJIB:
{"u":"UMK","f":"","s":"","m":{"n":"nama","i":"nim","p":"prodi","d":"dosen PA","t":"total sks","c":"tanggal"},"r":[["no","kelas","kode","nama mk","dosen","sks","senin","selasa","rabu","kamis","jumat","sabtu","minggu"]]}

ATURAN PENTING:
1. Urutan kolom jadwal WAJIB persis:
   senin=Sn, selasa=Sl, rabu=Rb, kamis=Km, jumat=Jm, sabtu=Sb, minggu=Mg.
2. Jangan menggeser jadwal ke kolom hari lain. Baca posisi sel pada tabel secara visual.
3. Jika sebuah sel hari kosong, isi "".
4. Jam dan ruang pada sel yang sama digabung singkat, contoh: "09:40-11:19 J.4,06".
5. Salin kode mata kuliah, kelas, nama mata kuliah, dosen, SKS, NIM, semester, dan total SKS SEAKURAT MUNGKIN dari dokumen. Jangan menebak.
6. Semua baris mata kuliah harus masuk tepat satu kali.
7. Sebelum mengeluarkan JSON, periksa ulang secara internal bahwa setiap jadwal berada pada kolom hari yang sama seperti tabel sumber.

${useText ? '\nSUMBER TEKS PDF:\n' + pdfText.slice(0,12000) : ''}`;

    const msg = { role:'user', content:PROMPT };
    if (!useText) msg.images = images;
    const payload = {
      model,
      messages: [msg],
      stream: false,
      think: false,
      format: 'json',
      keep_alive: '10m',
      options: { temperature: 0, num_ctx: useText ? 4096 : 12288, num_predict: useText ? 700 : 1100, top_k: 10, top_p: 0.9 }
    };

    const chatController = new AbortController();
    const chatTimer = setTimeout(() => chatController.abort(), useText ? 180000 : 360000);
    const res = await fetch(OLLAMA + '/api/chat', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload),
      signal: chatController.signal
    });
    clearTimeout(chatTimer);

    if (!res.ok) {
      const err = await res.text();
      throw new Error('Ollama error: ' + err.slice(0, 200));
    }

    setStep('Memproses data jadwal dari AI...');
    const data = await res.json();
    let raw = String(data.message?.content || '').trim();

    // Beberapa model kadang masih membungkus JSON dengan markdown.
    raw = raw.replace(/^```json\s*/i,'').replace(/^```\s*/i,'').replace(/\s*```$/i,'').trim();

    let jadwal;
    try {
      jadwal = JSON.parse(raw);
    } catch (e) {
      // Fallback: ambil blok JSON pertama jika model menyisipkan teks tambahan.
      const a = raw.indexOf('{');
      const b = raw.lastIndexOf('}');
      if (a >= 0 && b > a) {
        try { jadwal = JSON.parse(raw.slice(a, b + 1)); } catch (_) {}
      }
    }

    // Normalisasi schema ringkas -> schema tampilan.
    if (jadwal && Array.isArray(jadwal.r)) {
      const mm = jadwal.m || {};
      jadwal = {
        universitas: jadwal.u || 'Universitas Muria Kudus',
        fakultas: jadwal.f || '',
        semester: jadwal.s || '',
        mahasiswa: {
          nama: mm.n || '', nim: mm.i || '', prodi: mm.p || '', dosen_pa: mm.d || '',
          total_sks: mm.t || '', tanggal_cetak: mm.c || ''
        },
        mata_kuliah: jadwal.r.map((r,idx) => ({
          no: r?.[0] ?? String(idx+1), kelas: r?.[1] ?? '', kode_mk: r?.[2] ?? '',
          nama_mk: r?.[3] ?? '', dosen: r?.[4] ?? '', sks: r?.[5] ?? '',
          sn: r?.[6] ?? '', sl: r?.[7] ?? '', rb: r?.[8] ?? '', km: r?.[9] ?? '',
          jm: r?.[10] ?? '', sb: r?.[11] ?? '', mg: r?.[12] ?? ''
        }))
      };
    }

    if (!jadwal || !Array.isArray(jadwal.mata_kuliah)) {
      const thinking = String(data.message?.thinking || '').trim();
      const preview = raw || thinking || '(respons kosong)';
      throw new Error('AI belum menghasilkan JSON jadwal yang valid.\n\nOutput: ' + preview.slice(0,500));
    }

    // HTML dibuat oleh aplikasi, bukan oleh AI. Ini jauh lebih cepat dan tidak mudah terpotong.
    const html = buildScheduleHtml(jadwal);

    // 3. Simpan ke server
    setStep('Menyimpan ke server...');
    const meta = {
      nama: jadwal.mahasiswa?.nama || '',
      nim: jadwal.mahasiswa?.nim || '',
      prodi: jadwal.mahasiswa?.prodi || '',
      dosen_pa: jadwal.mahasiswa?.dosen_pa || '',
      sks: parseInt(jadwal.mahasiswa?.total_sks || 0, 10) || 0,
      semester: jadwal.semester || '',
      dicetak: jadwal.mahasiswa?.tanggal_cetak || ''
    };

    const sr = await fetch('api/jadwal.php?action=save_html', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({html, meta})
    });
    const sd = await sr.json();
    if (!sd.success) throw new Error('Gagal simpan ke DB: ' + sd.error);

    setStep('✓ Selesai!');
    await new Promise(r => setTimeout(r, 600));
    window.location.href = 'index.php';

  } catch(err) {
    document.getElementById('ov').classList.remove('show');
    showSb('error', '<strong>Gagal.</strong><br>' + err.message.replace(/\n/g,'<br>'));
    btn.disabled = false;
  }
}

// Init
checkOllama();
</script>
</body>
</html>
