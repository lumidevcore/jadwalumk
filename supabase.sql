-- ============================================
-- V6.2 - Supabase schema / migration
-- Aman dijalankan ulang di SQL Editor.
-- ============================================

CREATE TABLE IF NOT EXISTS settings (
  id         BIGSERIAL PRIMARY KEY,
  key        TEXT UNIQUE NOT NULL,
  value      TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS mahasiswa (
  id         BIGSERIAL PRIMARY KEY,
  nama       TEXT NOT NULL,
  nim        TEXT NOT NULL,
  prodi      TEXT,
  dosen_pa   TEXT,
  sks        INT,
  semester   TEXT,
  dicetak    TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS jadwal_upload (
  id           BIGSERIAL PRIMARY KEY,
  mahasiswa_id BIGINT REFERENCES mahasiswa(id) ON DELETE CASCADE,
  semester     TEXT NOT NULL,
  html_content TEXT NOT NULL,
  is_active    BOOLEAN DEFAULT TRUE,
  uploaded_at  TIMESTAMPTZ DEFAULT NOW()
);

-- mahasiswa adalah master data: satu NIM satu row.
CREATE UNIQUE INDEX IF NOT EXISTS uq_mahasiswa_nim ON mahasiswa(nim);
CREATE INDEX IF NOT EXISTS idx_jadwal_upload_active ON jadwal_upload(is_active, uploaded_at DESC);
CREATE INDEX IF NOT EXISTS idx_jadwal_upload_mhs ON jadwal_upload(mahasiswa_id);

CREATE OR REPLACE FUNCTION update_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_settings_updated ON settings;
CREATE TRIGGER trg_settings_updated
  BEFORE UPDATE ON settings
  FOR EACH ROW EXECUTE FUNCTION update_updated_at();

-- Trigger lama versi sebelumnya dinonaktifkan karena menonaktifkan jadwal
-- mahasiswa lain secara global. Sekarang API menonaktifkan hanya jadwal
-- lama milik mahasiswa yang sama sebelum insert jadwal baru.
DROP TRIGGER IF EXISTS trg_deactivate_old ON jadwal_upload;
DROP FUNCTION IF EXISTS deactivate_old_jadwal();

ALTER TABLE settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE mahasiswa ENABLE ROW LEVEL SECURITY;
ALTER TABLE jadwal_upload ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "allow_all_settings" ON settings;
DROP POLICY IF EXISTS "allow_all_mahasiswa" ON mahasiswa;
DROP POLICY IF EXISTS "allow_all_jadwal_upload" ON jadwal_upload;
CREATE POLICY "allow_all_settings" ON settings FOR ALL USING (true) WITH CHECK (true);
CREATE POLICY "allow_all_mahasiswa" ON mahasiswa FOR ALL USING (true) WITH CHECK (true);
CREATE POLICY "allow_all_jadwal_upload" ON jadwal_upload FOR ALL USING (true) WITH CHECK (true);
