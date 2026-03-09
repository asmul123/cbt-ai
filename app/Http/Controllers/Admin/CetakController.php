<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BeritaAcara;
use App\Models\PesertaUjian;
use App\Models\RuangUjian;
use App\Models\TandaTanganHadir;
use App\Models\Ujian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CetakController extends Controller
{
    /**
     * Halaman daftar ujian untuk cetak berita acara / daftar hadir
     */
    public function index(Request $request)
    {
        $ujianList = Ujian::whereIn('status', ['publish', 'berlangsung', 'selesai'])
            ->with('mapel')
            ->latest()
            ->get();

        $selectedUjian = null;
        $beritaAcaraList = collect();

        if ($request->ujian_id) {
            $selectedUjian = Ujian::with(['mapel', 'kelas', 'ruang'])->find($request->ujian_id);

            if ($selectedUjian) {
                $beritaAcaraList = BeritaAcara::where('ujian_id', $selectedUjian->id)
                    ->with(['ruangUjian', 'proktor'])
                    ->withCount('tandaTanganHadir')
                    ->get();
            }
        }

        return view('admin.cetak.index', compact('ujianList', 'selectedUjian', 'beritaAcaraList'));
    }

    /**
     * Cetak PDF Berita Acara per ruang
     */
    public function cetakBeritaAcara(BeritaAcara $beritaAcara)
    {
        $beritaAcara->load(['ujian.mapel', 'ujian.kelas', 'ruangUjian', 'proktor']);

        $ujian = $beritaAcara->ujian;

        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('ruang_ujian_id', $beritaAcara->ruang_ujian_id)
            ->with('siswa.kelas')
            ->get();

        $tidakHadirIds = $beritaAcara->peserta_tidak_hadir ?? [];
        $pesertaTidakHadir = $peserta->filter(fn($p) => in_array($p->id, $tidakHadirIds));

        $stats = [
            'total' => $peserta->count(),
            'hadir' => $peserta->count() - $pesertaTidakHadir->count(),
            'tidak_hadir' => $pesertaTidakHadir->count(),
        ];

        $tahunAjaran = $this->getTahunAjaran($ujian);

        // Ambil nama kelas hanya dari peserta di ruangan ini
        $kelasNames = $peserta->pluck('siswa.kelas.nama')->unique()->filter()->sort()->values()->implode(', ') ?: '-';

        $pdf = Pdf::loadView('exports.berita-acara', compact('beritaAcara', 'ujian', 'stats', 'tahunAjaran', 'pesertaTidakHadir', 'kelasNames'));
        return $pdf->download('Berita_Acara_' . str_replace(' ', '_', $ujian->nama_ujian) . '_' . str_replace(' ', '_', $beritaAcara->ruangUjian->nama) . '.pdf');
    }

    /**
     * Cetak PDF Daftar Hadir per ruang
     */
    public function cetakDaftarHadir(BeritaAcara $beritaAcara)
    {
        $beritaAcara->load(['ujian.mapel', 'ujian.kelas', 'ruangUjian', 'proktor']);

        $ujian = $beritaAcara->ujian;

        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('ruang_ujian_id', $beritaAcara->ruang_ujian_id)
            ->with('siswa.kelas')
            ->get()
            ->sortBy(fn($p) => $p->siswa->nis ?? '');

        $ttdMap = TandaTanganHadir::where('berita_acara_id', $beritaAcara->id)
            ->pluck('tanda_tangan', 'peserta_ujian_id');

        $tidakHadirIds = $beritaAcara->peserta_tidak_hadir ?? [];
        $tahunAjaran = $this->getTahunAjaran($ujian);

        // Ambil nama kelas hanya dari peserta di ruangan ini
        $kelasNames = $peserta->pluck('siswa.kelas.nama')->unique()->filter()->sort()->values()->implode(', ') ?: '-';

        $pdf = Pdf::loadView('exports.daftar-hadir', compact('beritaAcara', 'ujian', 'peserta', 'ttdMap', 'tidakHadirIds', 'tahunAjaran', 'kelasNames'));
        return $pdf->download('Daftar_Hadir_' . str_replace(' ', '_', $ujian->nama_ujian) . '_' . str_replace(' ', '_', $beritaAcara->ruangUjian->nama) . '.pdf');
    }

    private function getTahunAjaran(Ujian $ujian): string
    {
        $tgl = $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai) : now();
        $tahun = $tgl->year;
        $bulan = $tgl->month;

        return $bulan >= 7 ? $tahun . '-' . ($tahun + 1) : ($tahun - 1) . '-' . $tahun;
    }
}
