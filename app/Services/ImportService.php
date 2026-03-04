<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Mapel;
use App\Models\PesertaUjian;
use App\Models\RuangUjian;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ImportService
{
    /**
     * Import Kelas dari Excel
     * Format: nama | tingkat | jurusan (kode/nama) | tahun_ajaran
     */
    public function importKelas(UploadedFile $file): array
    {
        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong']];
        }

        $rows = $data[0];
        array_shift($rows); // skip header

        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            try {
                if (empty(trim($row[0] ?? ''))) continue;

                $nama = trim($row[0]);
                $tingkat = strtoupper(trim($row[1] ?? 'X'));
                $jurusanRef = trim($row[2] ?? '');
                $tahunAjaran = trim($row[3] ?? date('Y') . '/' . (date('Y') + 1));

                if (!in_array($tingkat, ['X', 'XI', 'XII'])) {
                    throw new \Exception("Tingkat '$tingkat' tidak valid (X/XI/XII).");
                }

                $jurusan = Jurusan::where('kode', $jurusanRef)
                    ->orWhere('nama', $jurusanRef)
                    ->first();

                if (!$jurusan) {
                    throw new \Exception("Jurusan '$jurusanRef' tidak ditemukan.");
                }

                Kelas::updateOrCreate(
                    ['nama' => $nama, 'jurusan_id' => $jurusan->id, 'tahun_ajaran' => $tahunAjaran],
                    ['tingkat' => $tingkat, 'is_active' => true]
                );
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris $rowNum: " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Import Ruang Ujian dari Excel
     * Format: kode | nama | kapasitas | lokasi
     */
    public function importRuang(UploadedFile $file): array
    {
        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong']];
        }

        $rows = $data[0];
        array_shift($rows);

        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            try {
                if (empty(trim($row[0] ?? ''))) continue;

                $kode = trim($row[0]);
                $nama = trim($row[1] ?? $kode);
                $kapasitas = intval($row[2] ?? 40);
                $lokasi = trim($row[3] ?? '');

                if ($kapasitas < 1) $kapasitas = 40;

                RuangUjian::updateOrCreate(
                    ['kode' => $kode],
                    [
                        'nama' => $nama,
                        'kapasitas' => $kapasitas,
                        'lokasi' => $lokasi ?: null,
                        'is_active' => true,
                    ]
                );
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris $rowNum: " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Import Guru dari Excel
     * Format: nip | nama | username | email | mapel (kode/nama) | no_hp | alamat | password
     */
    public function importGuru(UploadedFile $file): array
    {
        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong']];
        }

        $rows = $data[0];
        array_shift($rows);

        $success = 0;
        $failed = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            try {
                $nip = $this->cleanNumericString(trim($row[0] ?? ''));
                if (empty($nip)) continue;

                $nama = trim($row[1] ?? '');
                $username = trim($row[2] ?? '') ?: $nip;
                $email = trim($row[3] ?? '') ?: $nip . '@guru.cbt.local';
                $mapelRef = trim($row[4] ?? '');
                $noHp = $this->cleanNumericString(trim($row[5] ?? ''));
                $alamat = trim($row[6] ?? '');
                $password = trim($row[7] ?? '') ?: $nip;

                if (empty($nama)) {
                    throw new \Exception("Nama tidak boleh kosong.");
                }

                // Cari mapel (opsional)
                $mapel = null;
                if (!empty($mapelRef)) {
                    $mapel = Mapel::where('kode', $mapelRef)
                        ->orWhere('nama', $mapelRef)
                        ->first();
                    if (!$mapel) {
                        throw new \Exception("Mapel '$mapelRef' tidak ditemukan.");
                    }
                }

                DB::transaction(function () use ($nip, $nama, $username, $email, $mapel, $noHp, $alamat, $password, &$success, &$updated) {
                    $existingGuru = Guru::where('nip', $nip)->first();

                    if ($existingGuru) {
                        $existingGuru->update([
                            'nama' => $nama,
                            'mapel_id' => $mapel ? $mapel->id : $existingGuru->mapel_id,
                            'no_hp' => $noHp ?: $existingGuru->no_hp,
                            'alamat' => $alamat ?: $existingGuru->alamat,
                        ]);
                        $existingGuru->user->update(['name' => $nama]);
                        $updated++;
                    } else {
                        // Cek username & email unik
                        if (User::where('username', $username)->exists()) {
                            throw new \Exception("Username '$username' sudah digunakan.");
                        }
                        if (User::where('email', $email)->exists()) {
                            throw new \Exception("Email '$email' sudah digunakan.");
                        }

                        $user = User::create([
                            'name' => $nama,
                            'username' => $username,
                            'email' => $email,
                            'password' => Hash::make($password),
                            'is_active' => true,
                        ]);
                        $user->assignRole('guru');

                        Guru::create([
                            'user_id' => $user->id,
                            'nip' => $nip,
                            'nama' => $nama,
                            'mapel_id' => $mapel?->id,
                            'no_hp' => $noHp ?: null,
                            'alamat' => $alamat ?: null,
                        ]);
                        $success++;
                    }
                });
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris $rowNum: " . $e->getMessage();
            }
        }

        return ['success' => $success, 'updated' => $updated, 'failed' => $failed, 'errors' => $errors];
    }

    /**
     * Import Siswa dari Excel
     * Format: nis | nisn | nama | jenis_kelamin (L/P) | kelas (nama) | jurusan (kode/nama) | no_hp | alamat
     */
    public function importSiswa(UploadedFile $file): array
    {
        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong']];
        }

        $rows = $data[0];
        array_shift($rows);

        $success = 0;
        $failed = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            try {
                $nis = trim($row[0] ?? '');
                if (empty($nis)) continue;

                // NIS harus string, bukan float
                $nis = $this->cleanNumericString($nis);
                $nisn = $this->cleanNumericString(trim($row[1] ?? ''));
                $nama = trim($row[2] ?? '');
                $jk = strtoupper(trim($row[3] ?? 'L'));
                $kelasNama = trim($row[4] ?? '');
                $jurusanRef = trim($row[5] ?? '');
                $noHp = $this->cleanNumericString(trim($row[6] ?? ''));
                $alamat = trim($row[7] ?? '');

                if (empty($nama)) {
                    throw new \Exception("Nama tidak boleh kosong.");
                }

                if (!in_array($jk, ['L', 'P'])) {
                    throw new \Exception("Jenis kelamin '$jk' tidak valid (L/P).");
                }

                // Cari kelas
                $kelas = Kelas::where('nama', $kelasNama)->first();
                if (!$kelas) {
                    throw new \Exception("Kelas '$kelasNama' tidak ditemukan.");
                }

                // Cari jurusan — jika kosong, ambil dari kelas
                $jurusan = null;
                if (!empty($jurusanRef)) {
                    $jurusan = Jurusan::where('kode', $jurusanRef)
                        ->orWhere('nama', $jurusanRef)
                        ->first();
                    if (!$jurusan) {
                        throw new \Exception("Jurusan '$jurusanRef' tidak ditemukan.");
                    }
                } else {
                    $jurusan = $kelas->jurusan;
                }

                DB::transaction(function () use ($nis, $nisn, $nama, $jk, $kelas, $jurusan, $noHp, $alamat, &$success, &$updated) {
                    $existingSiswa = Siswa::where('nis', $nis)->first();

                    if ($existingSiswa) {
                        // Update existing
                        $existingSiswa->update([
                            'nisn' => $nisn ?: $existingSiswa->nisn,
                            'nama' => $nama,
                            'jenis_kelamin' => $jk,
                            'kelas_id' => $kelas->id,
                            'jurusan_id' => $jurusan->id,
                            'no_hp' => $noHp ?: $existingSiswa->no_hp,
                            'alamat' => $alamat ?: $existingSiswa->alamat,
                        ]);
                        $existingSiswa->user->update(['name' => $nama]);
                        $updated++;
                    } else {
                        // Create new user + siswa
                        $user = User::create([
                            'name' => $nama,
                            'username' => $nis,
                            'email' => $nis . '@siswa.cbt.local',
                            'password' => Hash::make($nis),
                            'is_active' => true,
                        ]);
                        $user->assignRole('siswa');

                        Siswa::create([
                            'user_id' => $user->id,
                            'nis' => $nis,
                            'nisn' => $nisn ?: null,
                            'nama' => $nama,
                            'jenis_kelamin' => $jk,
                            'kelas_id' => $kelas->id,
                            'jurusan_id' => $jurusan->id,
                            'no_hp' => $noHp ?: null,
                            'alamat' => $alamat ?: null,
                        ]);
                        $success++;
                    }
                });
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris $rowNum: " . $e->getMessage();
            }
        }

        return ['success' => $success, 'updated' => $updated, 'failed' => $failed, 'errors' => $errors];
    }

    /**
     * Import Distribusi Ruang dari Excel
     * Format: nis | kode_ruang
     * Atau:   kelas (nama) | kode_ruang  (distribusi massal per kelas)
     */
    public function importDistribusiRuang(UploadedFile $file, string $mode = 'siswa'): array
    {
        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong']];
        }

        $rows = $data[0];
        array_shift($rows);

        $success = 0;
        $failed = 0;
        $errors = [];

        if ($mode === 'kelas') {
            return $this->distribusiPerKelas($rows, $success, $failed, $errors);
        }

        // Mode per-siswa: nis | kode_ruang
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            try {
                $nis = $this->cleanNumericString(trim($row[0] ?? ''));
                $kodeRuang = trim($row[1] ?? '');

                if (empty($nis)) continue;

                $siswa = Siswa::where('nis', $nis)->first();
                if (!$siswa) {
                    throw new \Exception("Siswa dengan NIS '$nis' tidak ditemukan.");
                }

                $ruang = RuangUjian::where('kode', $kodeRuang)->first();
                if (!$ruang) {
                    throw new \Exception("Ruang dengan kode '$kodeRuang' tidak ditemukan.");
                }

                // Cek kapasitas
                $terisi = Siswa::where('ruang_ujian_id', $ruang->id)->count();
                if ($terisi >= $ruang->kapasitas && $siswa->ruang_ujian_id !== $ruang->id) {
                    throw new \Exception("Ruang '{$ruang->nama}' sudah penuh ({$terisi}/{$ruang->kapasitas}).");
                }

                $siswa->update(['ruang_ujian_id' => $ruang->id]);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris $rowNum: " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Distribusi ruang per kelas: kelas (nama) | kode_ruang
     */
    private function distribusiPerKelas(array $rows, int $success, int $failed, array $errors): array
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            try {
                $kelasNama = trim($row[0] ?? '');
                $kodeRuang = trim($row[1] ?? '');

                if (empty($kelasNama)) continue;

                $kelas = Kelas::where('nama', $kelasNama)->first();
                if (!$kelas) {
                    throw new \Exception("Kelas '$kelasNama' tidak ditemukan.");
                }

                $ruang = RuangUjian::where('kode', $kodeRuang)->first();
                if (!$ruang) {
                    throw new \Exception("Ruang dengan kode '$kodeRuang' tidak ditemukan.");
                }

                $siswaKelas = Siswa::where('kelas_id', $kelas->id)->get();
                $terisi = Siswa::where('ruang_ujian_id', $ruang->id)->count();
                $sisa = $ruang->kapasitas - $terisi;

                if ($siswaKelas->count() > $sisa) {
                    throw new \Exception("Kapasitas ruang '{$ruang->nama}' tidak cukup. Sisa: $sisa, perlu: {$siswaKelas->count()}.");
                }

                Siswa::where('kelas_id', $kelas->id)->update(['ruang_ujian_id' => $ruang->id]);
                $success += $siswaKelas->count();
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris $rowNum: " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Clean numeric string — Excel sometimes reads "00123" as 123.0
     */
    private function cleanNumericString(string $value): string
    {
        if (is_numeric($value) && str_contains($value, '.')) {
            $value = rtrim(rtrim($value, '0'), '.');
        }
        return $value;
    }

    /**
     * Import Proktor dari Excel
     * Format: nama | username | ruang (kode) | password
     */
    public function importProktor(UploadedFile $file): array
    {
        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong']];
        }

        $rows = $data[0];
        array_shift($rows);

        $success = 0;
        $failed = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            try {
                $nama = trim($row[0] ?? '');
                if (empty($nama)) continue;

                $username = trim($row[1] ?? '');
                $ruangKode = trim($row[2] ?? '');
                $password = trim($row[3] ?? '') ?: $username;

                if (empty($username)) {
                    throw new \Exception("Username tidak boleh kosong.");
                }

                // Cari ruang (opsional)
                $ruangId = null;
                if (!empty($ruangKode)) {
                    $ruang = RuangUjian::where('kode', $ruangKode)->first();
                    if (!$ruang) {
                        throw new \Exception("Ruang dengan kode '$ruangKode' tidak ditemukan.");
                    }
                    $ruangId = $ruang->id;
                }

                DB::transaction(function () use ($nama, $username, $ruangId, $password, &$success, &$updated) {
                    $existingUser = User::where('username', $username)->first();

                    if ($existingUser && $existingUser->hasRole('proktor')) {
                        $existingUser->update([
                            'name' => $nama,
                            'ruang_ujian_id' => $ruangId ?? $existingUser->ruang_ujian_id,
                        ]);
                        $updated++;
                    } else if ($existingUser) {
                        throw new \Exception("Username '$username' sudah digunakan oleh user lain.");
                    } else {
                        $user = User::create([
                            'name' => $nama,
                            'username' => $username,
                            'email' => $username . '@proktor.cbt.local',
                            'password' => Hash::make($password),
                            'is_active' => true,
                            'ruang_ujian_id' => $ruangId,
                        ]);
                        $user->assignRole('proktor');
                        $success++;
                    }
                });
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris $rowNum: " . $e->getMessage();
            }
        }

        return ['success' => $success, 'updated' => $updated, 'failed' => $failed, 'errors' => $errors];
    }

    // ==================== RESET METHODS ====================

    /**
     * Reset (hapus semua) data siswa beserta akun user-nya
     */
    public function resetSiswa(): array
    {
        return DB::transaction(function () {
            $siswa = Siswa::with('user')->get();
            $count = $siswa->count();

            foreach ($siswa as $s) {
                $s->user?->delete(); // cascade akan hapus siswa juga
            }

            return ['deleted' => $count, 'type' => 'Siswa'];
        });
    }

    /**
     * Reset (hapus semua) data guru beserta akun user-nya
     */
    public function resetGuru(): array
    {
        return DB::transaction(function () {
            $guru = Guru::with('user')->get();
            $count = $guru->count();

            foreach ($guru as $g) {
                $g->user?->delete(); // cascade akan hapus guru juga
            }

            return ['deleted' => $count, 'type' => 'Guru'];
        });
    }

    /**
     * Reset (hapus semua) data proktor
     */
    public function resetProktor(): array
    {
        return DB::transaction(function () {
            $proktor = User::role('proktor')->get();
            $count = $proktor->count();

            foreach ($proktor as $p) {
                $p->delete();
            }

            return ['deleted' => $count, 'type' => 'Proktor'];
        });
    }

    /**
     * Reset (hapus semua) data ruang ujian
     * Juga mengosongkan ruang_ujian_id di siswa & user (proktor)
     */
    public function resetRuang(): array
    {
        return DB::transaction(function () {
            // Kosongkan referensi dulu
            Siswa::whereNotNull('ruang_ujian_id')->update(['ruang_ujian_id' => null]);
            User::whereNotNull('ruang_ujian_id')->update(['ruang_ujian_id' => null]);

            $count = RuangUjian::count();
            RuangUjian::query()->delete();

            return ['deleted' => $count, 'type' => 'Ruang Ujian'];
        });
    }

    /**
     * Reset (hapus semua) data kelas
     * Siswa yang terkait kelas akan ikut terhapus (cascade)
     */
    public function resetKelas(): array
    {
        return DB::transaction(function () {
            $count = Kelas::count();
            Kelas::query()->delete();

            return ['deleted' => $count, 'type' => 'Kelas'];
        });
    }
}
