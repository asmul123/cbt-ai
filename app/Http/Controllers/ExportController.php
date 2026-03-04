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

    public function pdfBeritaAcara(Ujian $ujian)
    {
        $ujian->load(['mapel', 'guru', 'kelas', 'peserta.siswa']);

        $stats = [
            'total' => $ujian->peserta->count(),
            'mengerjakan' => $ujian->peserta->where('status', 'mengerjakan')->count(),
            'selesai' => $ujian->peserta->where('status', 'selesai')->count(),
            'belum' => $ujian->peserta->where('status', 'belum_mulai')->count(),
            'rata_rata' => \App\Models\HasilUjian::where('ujian_id', $ujian->id)->avg('nilai_akhir') ?? 0,
            'tertinggi' => \App\Models\HasilUjian::where('ujian_id', $ujian->id)->max('nilai_akhir') ?? 0,
            'terendah' => \App\Models\HasilUjian::where('ujian_id', $ujian->id)->min('nilai_akhir') ?? 0,
        ];

        $pdf = Pdf::loadView('exports.berita-acara', compact('ujian', 'stats'));
        return $pdf->download('Berita_Acara_' . str_replace(' ', '_', $ujian->nama_ujian) . '.pdf');
    }
}
