<?php

namespace App\Services;

use App\Models\Ujian;
use App\Models\Soal;
use App\Models\PesertaUjian;
use App\Models\JawabanSiswa;
use App\Models\HasilUjian;
use App\Models\LogAktivitas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UjianService
{
    /**
     * Mulai ujian untuk siswa
     */
    public function mulaiUjian(Ujian $ujian, Siswa $siswa, string $ipAddress): PesertaUjian
    {
        return DB::transaction(function () use ($ujian, $siswa, $ipAddress) {
            // Buat/update peserta ujian
            $peserta = PesertaUjian::updateOrCreate(
                ['ujian_id' => $ujian->id, 'siswa_id' => $siswa->id],
                [
                    'status' => 'mengerjakan',
                    'waktu_mulai' => now(),
                    'ip_address' => $ipAddress,
                    'soal_order' => $this->generateSoalOrder($ujian),
                ]
            );

            // Log aktivitas
            LogAktivitas::log($siswa->user_id, 'mulai_ujian', $ujian->id, 'Siswa memulai ujian');

            return $peserta;
        });
    }

    /**
     * Generate urutan soal (acak atau tidak)
     */
    public function generateSoalOrder(Ujian $ujian): array
    {
        $soalIds = $ujian->soal()->pluck('soal.id')->toArray();

        if ($ujian->acak_soal) {
            shuffle($soalIds);
        }

        if ($ujian->jumlah_soal_tampil && $ujian->jumlah_soal_tampil < count($soalIds)) {
            $soalIds = array_slice($soalIds, 0, $ujian->jumlah_soal_tampil);
        }

        return $soalIds;
    }

    /**
     * Simpan jawaban siswa
     */
    public function simpanJawaban(PesertaUjian $peserta, int $soalId, ?string $jawaban, bool $raguRagu = false): JawabanSiswa
    {
        return JawabanSiswa::updateOrCreate(
            [
                'peserta_ujian_id' => $peserta->id,
                'soal_id' => $soalId,
            ],
            [
                'jawaban' => $jawaban,
                'ragu_ragu' => $raguRagu,
            ]
        );
    }

    /**
     * Submit ujian dan hitung nilai
     */
    public function submitUjian(PesertaUjian $peserta): HasilUjian
    {
        return DB::transaction(function () use ($peserta) {
            $peserta->update([
                'status' => 'selesai',
                'waktu_selesai' => now(),
            ]);

            // Hitung nilai otomatis
            $hasil = $this->hitungNilai($peserta);

            // Log
            LogAktivitas::log(
                $peserta->siswa->user_id,
                'submit_ujian',
                $peserta->ujian_id,
                'Siswa menyelesaikan ujian. Nilai: ' . $hasil->nilai_akhir
            );

            return $hasil;
        });
    }

    /**
     * Hitung nilai otomatis
     */
    public function hitungNilai(PesertaUjian $peserta): HasilUjian
    {
        $ujian = $peserta->ujian;
        $jawaban = $peserta->jawabanSiswa()->with('soal.opsi')->get();

        $totalSoal = $jawaban->count();
        $benarPG = 0;
        $skorPG = 0;
        $skorIsian = 0;
        $skorEssay = 0;
        $totalBobotPG = 0;
        $totalBobotIsian = 0;
        $totalBobotEssay = 0;

        foreach ($jawaban as $jwb) {
            $soal = $jwb->soal;

            switch ($soal->tipe_soal) {
                case 'pg':
                    $totalBobotPG += $soal->bobot;
                    $opsiBenar = $soal->opsi->where('is_benar', true)->first();
                    if ($opsiBenar && $jwb->jawaban == $opsiBenar->label) {
                        $benarPG++;
                        $skorPG += $soal->bobot;
                        $jwb->update(['is_benar' => true, 'skor' => $soal->bobot]);
                    } else {
                        $jwb->update(['is_benar' => false, 'skor' => 0]);
                    }
                    break;

                case 'pg_kompleks':
                    $totalBobotPG += $soal->bobot;
                    $opsiBenarLabels = $soal->opsi->where('is_benar', true)->pluck('label')->sort()->values()->toArray();
                    $jawabanLabels = collect(json_decode($jwb->jawaban, true) ?? [])->sort()->values()->toArray();
                    if ($opsiBenarLabels === $jawabanLabels) {
                        $benarPG++;
                        $skorPG += $soal->bobot;
                        $jwb->update(['is_benar' => true, 'skor' => $soal->bobot]);
                    } else {
                        $jwb->update(['is_benar' => false, 'skor' => 0]);
                    }
                    break;

                case 'isian':
                    $totalBobotIsian += $soal->bobot;
                    $opsiBenar = $soal->opsi->where('is_benar', true)->first();
                    if ($opsiBenar && strtolower(trim($jwb->jawaban)) === strtolower(trim($opsiBenar->teks))) {
                        $skorIsian += $soal->bobot;
                        $jwb->update(['is_benar' => true, 'skor' => $soal->bobot]);
                    } else {
                        $jwb->update(['is_benar' => false, 'skor' => 0]);
                    }
                    break;

                case 'essay':
                    $totalBobotEssay += $soal->bobot;
                    // Essay dinilai manual oleh guru, ambil skor jika sudah dinilai
                    if ($jwb->skor !== null) {
                        $skorEssay += $jwb->skor;
                    }
                    break;
            }
        }

        // Hitung nilai akhir (jika ada essay, masih belum final)
        $totalBobot = $totalBobotPG + $totalBobotIsian + $totalBobotEssay;
        $skorTotal = $skorPG + $skorIsian + $skorEssay;
        $nilaiAkhir = $totalBobot > 0 ? round(($skorTotal / $totalBobot) * 100, 2) : 0;

        // Check if all essay have been scored
        $essayBelumDinilai = $jawaban->filter(fn($j) => $j->soal->tipe_soal === 'essay' && $j->skor === null)->count();
        $statusKelulusan = $essayBelumDinilai > 0 ? 'belum_dinilai' : ($nilaiAkhir >= $ujian->kkm ? 'lulus' : 'tidak_lulus');

        $hasil = HasilUjian::updateOrCreate(
            ['ujian_id' => $ujian->id, 'siswa_id' => $peserta->siswa_id],
            [
                'peserta_ujian_id' => $peserta->id,
                'jumlah_soal' => $totalSoal,
                'benar_pg' => $benarPG,
                'skor_pg' => $totalBobotPG > 0 ? round(($skorPG / $totalBobotPG) * 100, 2) : 0,
                'skor_essay' => $totalBobotEssay > 0 ? round(($skorEssay / $totalBobotEssay) * 100, 2) : 0,
                'skor_isian' => $totalBobotIsian > 0 ? round(($skorIsian / $totalBobotIsian) * 100, 2) : 0,
                'nilai_akhir' => $nilaiAkhir,
                'status_kelulusan' => $statusKelulusan,
                'waktu_mulai' => $peserta->waktu_mulai,
                'waktu_selesai' => $peserta->waktu_selesai,
                'durasi_pengerjaan' => $peserta->waktu_mulai
                    ? (int) abs(($peserta->waktu_selesai ?? now())->diffInSeconds($peserta->waktu_mulai))
                    : null,
            ]
        );

        // Cache hasil ujian selama 24 jam
        Cache::put("hasil_ujian:{$peserta->siswa_id}:{$ujian->id}", $hasil, now()->addHours(24));

        return $hasil;
    }

    /**
     * Penilaian essay oleh guru
     */
    public function nilaiEssay(JawabanSiswa $jawaban, float $skor): void
    {
        $soal = $jawaban->soal;
        $jawaban->update([
            'skor' => $skor,
            'is_benar' => $skor > 0,
        ]);

        // Recalculate hasil ujian
        $peserta = $jawaban->pesertaUjian;
        $this->hitungNilai($peserta);
    }

    /**
     * Generate token ujian
     */
    public function generateToken(): string
    {
        return Ujian::generateToken();
    }

    /**
     * Cek apakah ujian bisa diakses
     */
    public function cekAksesUjian(Ujian $ujian, Siswa $siswa, string $ipAddress): array
    {
        $errors = [];

        if (!$ujian->isAktif()) {
            $errors[] = 'Ujian belum aktif atau sudah selesai.';
        }

        if (now()->lt($ujian->tanggal_mulai)) {
            $errors[] = 'Ujian belum dimulai.';
        }

        if (now()->gt($ujian->tanggal_selesai)) {
            $errors[] = 'Waktu ujian sudah berakhir.';
        }

        // Cek kelas siswa terdaftar
        $kelasIds = $ujian->kelas()->pluck('kelas.id')->toArray();
        if (!in_array($siswa->kelas_id, $kelasIds)) {
            $errors[] = 'Kelas Anda tidak terdaftar untuk ujian ini.';
        }

        // Cek batasan IP
        if ($ujian->batasi_ip && $ujian->ip_allowed) {
            $allowedIps = array_map('trim', explode(',', $ujian->ip_allowed));
            if (!in_array($ipAddress, $allowedIps)) {
                $errors[] = 'IP Address Anda tidak diizinkan.';
            }
        }

        // Cek apakah sudah submit
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->first();
        if ($peserta && $peserta->isSelesai()) {
            $errors[] = 'Anda sudah menyelesaikan ujian ini.';
        }

        return $errors;
    }
}
