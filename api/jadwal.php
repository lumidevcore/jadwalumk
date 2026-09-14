<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../config/supabase.php';
$action = $_GET['action'] ?? '';

function out(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_html') {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $html = trim((string)($body['html'] ?? ''));
        $meta = is_array($body['meta'] ?? null) ? $body['meta'] : [];

        if (strlen($html) < 500) out(['success'=>false,'error'=>'HTML terlalu pendek.'], 400);
        if (empty($meta['nim'])) out(['success'=>false,'error'=>'NIM tidak terbaca. Data mahasiswa tidak dapat disimpan.'], 400);

        $nim = trim((string)$meta['nim']);
        $studentData = [
            'nama'     => trim((string)($meta['nama'] ?? '')),
            'nim'      => $nim,
            'prodi'    => trim((string)($meta['prodi'] ?? '')),
            'dosen_pa' => trim((string)($meta['dosen_pa'] ?? '')),
            'sks'      => (int)($meta['sks'] ?? 0),
            'semester' => trim((string)($meta['semester'] ?? '')),
            'dicetak'  => trim((string)($meta['dicetak'] ?? '')),
        ];

        // mahasiswa sekarang benar-benar dipakai sebagai master data mahasiswa.
        $existing = sbGet('mahasiswa', 'nim', $nim);
        if ($existing) {
            sbUpdate('mahasiswa', ['nim' => 'eq.' . $nim], $studentData);
            $mahasiswaId = $existing['id'];
        } else {
            $rows = sbInsert('mahasiswa', $studentData);
            $mahasiswaId = $rows[0]['id'] ?? null;
        }
        if (!$mahasiswaId) throw new RuntimeException('Gagal mendapatkan ID mahasiswa dari Supabase.');

        // Hanya nonaktifkan jadwal lama milik mahasiswa yang sama.
        sbUpdate('jadwal_upload', [
            'mahasiswa_id' => 'eq.' . $mahasiswaId,
            'is_active' => 'eq.true'
        ], ['is_active' => false]);

        $uploads = sbInsert('jadwal_upload', [
            'mahasiswa_id' => $mahasiswaId,
            'semester'     => $studentData['semester'] ?: 'Unknown',
            'html_content' => $html,
            'is_active'    => true,
        ]);

        // Cache halaman aktif + identitas pemilik jadwal aktif.
        sbUpsert('settings', ['key' => 'jadwal_html', 'value' => $html]);
        sbUpsert('settings', ['key' => 'jadwal_nim', 'value' => $nim]);
        sbUpsert('settings', ['key' => 'jadwal_mahasiswa_id', 'value' => (string)$mahasiswaId]);

        out([
            'success'=>true,
            'mahasiswa_id'=>$mahasiswaId,
            'jadwal_upload_id'=>$uploads[0]['id'] ?? null,
            'nim'=>$nim
        ]);
    }

    if ($action === 'has_html') {
        $row = sbGet('settings', 'key', 'jadwal_html');
        out(['success'=>true,'has_html'=>(bool)$row]);
    }

    // Dipakai untuk debugging/cek bahwa tabel mahasiswa sudah berfungsi.
    if ($action === 'current_student') {
        $nimSetting = sbGet('settings', 'key', 'jadwal_nim');
        $nim = $nimSetting['value'] ?? '';
        $mhs = $nim ? sbGet('mahasiswa', 'nim', $nim) : null;
        out(['success'=>true,'mahasiswa'=>$mhs]);
    }

    out(['success'=>false,'error'=>'Action tidak ditemukan.'], 404);
} catch (Throwable $e) {
    out(['success'=>false,'error'=>$e->getMessage()], 500);
}
