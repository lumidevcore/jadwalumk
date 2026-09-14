<?php
// ── Supabase Config ──────────────────────────────
// Isi dengan kredensial dari:
// Dashboard → Settings → API
define('SUPABASE_URL', 'https://ymrmkabjvcwjfqutdxjs.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inltcm1rYWJqdmN3amZxdXRkeGpzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODkzMjk5MTcsImV4cCI6MjEwNDkwNTkxN30.nC8OM7eWtpiRaCm7Q_DFe6X64hWXAq_llpfLPmNf6RE');  // anon public key

// ── Helper: request ke Supabase REST API ─────────
function sb(string $method, string $table, array $params = [], ?array $body = null, bool $upsert = false): array {
    $url = SUPABASE_URL . '/rest/v1/' . $table;
    if ($params) $url .= '?' . http_build_query($params);

    $headers = [
        'apikey: '        . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    if ($upsert)   $headers[] = 'Prefer: resolution=merge-duplicates,return=representation';
    elseif ($body) $headers[] = 'Prefer: return=representation';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 15,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) throw new RuntimeException('cURL: ' . $err);
    $data = json_decode($res, true) ?? [];
    if ($code >= 400) {
        $msg = is_array($data) ? ($data['message'] ?? $res) : $res;
        throw new RuntimeException("Supabase [$code]: $msg");
    }
    return $data;
}

// ── Shortcut functions ────────────────────────────

/** Ambil 1 row dari tabel berdasarkan kolom = nilai */
function sbGet(string $table, string $col, string $val): ?array {
    $rows = sb('GET', $table, [$col => 'eq.' . $val, 'limit' => '1']);
    return $rows[0] ?? null;
}

/** Upsert (insert or update) berdasarkan kolom unik */
function sbUpsert(string $table, array $body): array {
    return sb('POST', $table, [], $body, true);
}

/** Insert biasa */
function sbInsert(string $table, array $body): array {
    return sb('POST', $table, [], $body);
}

/** Update rows berdasarkan filter */
function sbUpdate(string $table, array $filter, array $body): array {
    return sb('PATCH', $table, $filter, $body);
}
