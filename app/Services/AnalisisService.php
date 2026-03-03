<?php

namespace App\Services;

use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\Soal;
use App\Models\Ujian;
use Illuminate\Support\Collection;

class AnalisisService
{
    /**
     * Tingkat Kesukaran: P = B / JS
     * B = Jumlah siswa yang menjawab benar
     * JS = Jumlah seluruh siswa
     */
    public function tingkatKesukaran(Soal $soal, Ujian $ujian): array
    {
        $jawaban = JawabanSiswa::whereHas('pesertaUjian', function ($q) use ($ujian) {
            $q->where('ujian_id', $ujian->id)->where('status', 'selesai');
        })->where('soal_id', $soal->id)->get();

        $totalSiswa = $jawaban->count();
        if ($totalSiswa === 0) {
            return ['p' => 0, 'kategori' => 'Tidak ada data'];
        }

        $benar = $jawaban->where('is_benar', true)->count();
        $p = round($benar / $totalSiswa, 2);

        $kategori = match (true) {
            $p < 0.30 => 'Sulit',
            $p <= 0.70 => 'Sedang',
            default => 'Mudah',
        };

        return ['p' => $p, 'benar' => $benar, 'total' => $totalSiswa, 'kategori' => $kategori];
    }

    /**
     * Daya Pembeda: D = (BA - BB) / (JS/2)
     * BA = Jumlah benar kelompok atas
     * BB = Jumlah benar kelompok bawah
     */
    public function dayaPembeda(Soal $soal, Ujian $ujian): array
    {
        $hasil = HasilUjian::where('ujian_id', $ujian->id)
            ->orderByDesc('nilai_akhir')
            ->get();

        $total = $hasil->count();
        if ($total < 2) {
            return ['d' => 0, 'kategori' => 'Tidak ada data'];
        }

        $separuh = (int)ceil($total / 2);
        $kelompokAtas = $hasil->take($separuh)->pluck('siswa_id');
        $kelompokBawah = $hasil->skip($separuh)->pluck('siswa_id');

        $benarAtas = JawabanSiswa::whereHas('pesertaUjian', function ($q) use ($ujian, $kelompokAtas) {
            $q->where('ujian_id', $ujian->id)->whereIn('siswa_id', $kelompokAtas);
        })->where('soal_id', $soal->id)->where('is_benar', true)->count();

        $benarBawah = JawabanSiswa::whereHas('pesertaUjian', function ($q) use ($ujian, $kelompokBawah) {
            $q->where('ujian_id', $ujian->id)->whereIn('siswa_id', $kelompokBawah);
        })->where('soal_id', $soal->id)->where('is_benar', true)->count();

        $d = $separuh > 0 ? round(($benarAtas - $benarBawah) / $separuh, 2) : 0;

        $kategori = match (true) {
            $d < 0 => 'Sangat Jelek (Negatif)',
            $d < 0.20 => 'Jelek',
            $d < 0.40 => 'Cukup',
            $d < 0.70 => 'Baik',
            default => 'Sangat Baik',
        };

        return ['d' => $d, 'ba' => $benarAtas, 'bb' => $benarBawah, 'kategori' => $kategori];
    }

    /**
     * Distribusi jawaban per soal
     */
    public function distribusiJawaban(Soal $soal, Ujian $ujian): array
    {
        $jawaban = JawabanSiswa::whereHas('pesertaUjian', function ($q) use ($ujian) {
            $q->where('ujian_id', $ujian->id)->where('status', 'selesai');
        })->where('soal_id', $soal->id)->get();

        $distribusi = [];
        $opsi = $soal->opsi;

        foreach ($opsi as $o) {
            $distribusi[$o->label] = [
                'label' => $o->label,
                'teks' => $o->teks,
                'jumlah' => $jawaban->where('jawaban', $o->id)->count(),
                'is_benar' => $o->is_benar,
            ];
        }

        $distribusi['kosong'] = [
            'label' => 'Kosong',
            'teks' => 'Tidak menjawab',
            'jumlah' => $jawaban->whereNull('jawaban')->count(),
            'is_benar' => false,
        ];

        return $distribusi;
    }

    /**
     * Statistik ujian per kelas
     */
    public function statistikUjian(Ujian $ujian, ?int $kelasId = null): array
    {
        $query = HasilUjian::where('ujian_id', $ujian->id);

        if ($kelasId) {
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        $hasil = $query->get();

        if ($hasil->isEmpty()) {
            return [
                'rata_rata' => 0,
                'tertinggi' => 0,
                'terendah' => 0,
                'lulus' => 0,
                'tidak_lulus' => 0,
                'total_peserta' => 0,
                'std_deviasi' => 0,
            ];
        }

        $nilai = $hasil->pluck('nilai_akhir');
        $avg = $nilai->avg();
        $stdDev = $this->standardDeviation($nilai->toArray());

        return [
            'rata_rata' => round($avg, 2),
            'tertinggi' => $nilai->max(),
            'terendah' => $nilai->min(),
            'lulus' => $hasil->where('status_kelulusan', 'lulus')->count(),
            'tidak_lulus' => $hasil->where('status_kelulusan', 'tidak_lulus')->count(),
            'total_peserta' => $hasil->count(),
            'std_deviasi' => round($stdDev, 2),
        ];
    }

    /**
     * Ranking siswa per ujian
     */
    public function ranking(Ujian $ujian, ?int $kelasId = null): Collection
    {
        $query = HasilUjian::where('ujian_id', $ujian->id)
            ->with(['siswa.kelas', 'siswa.jurusan'])
            ->orderByDesc('nilai_akhir');

        if ($kelasId) {
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        return $query->get();
    }

    /**
     * Hitung standard deviasi
     */
    private function standardDeviation(array $data): float
    {
        $n = count($data);
        if ($n <= 1) return 0;

        $mean = array_sum($data) / $n;
        $sumSquaredDiff = array_sum(array_map(fn($x) => pow($x - $mean, 2), $data));

        return sqrt($sumSquaredDiff / ($n - 1));
    }
}
