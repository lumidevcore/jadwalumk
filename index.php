<?php
require_once __DIR__ . '/config/supabase.php';

try {
    $row = sbGet('settings', 'key', 'jadwal_html');
} catch (Throwable $e) {
    $row = null;
    $dbError = $e->getMessage();
}

if ($row && strlen($row['value'] ?? '') > 500) {
    echo $row['value'];
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Jadwal Kuliah – UMK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f0f2f7;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.empty{text-align:center;padding:48px 24px;max-width:400px}
.ic{width:68px;height:68px;background:#0d1f3c;border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px}
h2{font-size:19px;font-weight:800;color:#1a2540;margin-bottom:10px}
p{color:#7a84a0;font-size:14px;line-height:1.6;margin-bottom:24px}
.btn{display:inline-flex;align-items:center;gap:8px;background:#0d1f3c;color:#fff;text-decoration:none;font-size:14px;font-weight:700;padding:14px 28px;border-radius:12px}
.err{background:#fff0f0;border:1px solid #ffb0b0;color:#8b0000;border-radius:10px;padding:12px 14px;font-size:12px;margin-bottom:18px;text-align:left;line-height:1.6}
</style>
</head>
<body>
<div class="empty">
  <div class="ic">
    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2">
      <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
      <polyline points="14 2 14 8 20 8"/>
      <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
    </svg>
  </div>
  <?php if (!empty($dbError)): ?>
  <div class="err"><strong>⚠ Koneksi Supabase gagal:</strong><br><?= htmlspecialchars($dbError) ?><br><br>Pastikan SUPABASE_KEY sudah diisi di <code>config/supabase.php</code></div>
  <?php endif; ?>
  <h2>Belum ada jadwal</h2>
  <p>Upload file HTML jadwal untuk mulai<br>menampilkan jadwal dari semua device.</p>
  <a href="upload.php" class="btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
      <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
    </svg>
    Upload Jadwal
  </a>
</div>
</body>
</html>
