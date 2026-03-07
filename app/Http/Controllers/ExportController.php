<?php

namespace App\Http\Controllers;

use App\Exports\HasilUjianExport;
use App\Models\Ujian;
use App\Models\HasilUjian;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function excelHasil(Ujian $ujian, Request $request)
    {
        $filename = 'Hasil_' . str_replace(' ', '_', $ujian->nama_ujian) . '.xlsx';
        return Excel::download(new HasilUjianExport($ujian->id, $request->kelas_id), $filename);
    }

    public function pdfHasil(Ujian $ujian, Request $request)
    {
        $query = HasilUjian::where('ujian_id', $ujian->id)
            ->with(['siswa.kelas', 'siswa.jurusan'])
            ->orderByDesc('nilai_akhir');

        if ($request->kelas_id) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }

        $hasil = $query->get();
        $ujian->load('mapel');

        $pdf = Pdf::loadView('exports.hasil-ujian', compact('ujian', 'hasil'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('Hasil_' . str_replace(' ', '_', $ujian->nama_ujian) . '.pdf');
    }

    public function pdfBeritaAcara(Ujian $ujian, Request $request)
    {
        $ujian->load(['mapel', 'guru', 'kelas', 'ruang', 'peserta.siswa']);
        $ujian->loadCount('soal');

        $stats = [
            'total' => $ujian->peserta->count(),
            'mengerjakan' => $ujian->peserta->where('status', 'mengerjakan')->count(),
            'selesai' => $ujian->peserta->where('status', 'selesai')->count(),
            'belum' => $ujian->peserta->where('status', 'belum_mulai')->count(),
        ];

        $tahunAjaran = $this->getTahunAjaran($ujian);
        $pengawas = $request->input('pengawas', '');
        $catatan = $request->input('catatan') ?: 'Aman';

        $pdf = Pdf::loadView('exports.berita-acara', compact('ujian', 'stats', 'tahunAjaran', 'pengawas', 'catatan'));
        return $pdf->download('Berita_Acara_' . str_replace(' ', '_', $ujian->nama_ujian) . '.pdf');
    }

    public function pdfDaftarHadir(Ujian $ujian, Request $request)
    {
        $ujian->load(['mapel', 'guru', 'kelas', 'ruang', 'peserta.siswa']);

        $peserta = $ujian->peserta->sortBy(fn($p) => $p->siswa->nis ?? '');

        $tahunAjaran = $this->getTahunAjaran($ujian);
        $pengawas = $request->input('pengawas', '');

        $pdf = Pdf::loadView('exports.daftar-hadir', compact('ujian', 'peserta', 'tahunAjaran', 'pengawas'));
        return $pdf->download('Daftar_Hadir_' . str_replace(' ', '_', $ujian->nama_ujian) . '.pdf');
    }

    /**
     * Hitung tahun ajaran berdasarkan tanggal ujian.
     * Juli-Desember = tahun/tahun+1, Januari-Juni = tahun-1/tahun
     */
    private function getTahunAjaran(Ujian $ujian): string
    {
        $tgl = $ujian->tanggal_mulai ? \Carbon\Carbon::parse($ujian->tanggal_mulai) : now();
        $tahun = $tgl->year;
        $bulan = $tgl->month;

        if ($bulan >= 7) {
            return $tahun . '-' . ($tahun + 1);
        }
        return ($tahun - 1) . '-' . $tahun;
    }
}
