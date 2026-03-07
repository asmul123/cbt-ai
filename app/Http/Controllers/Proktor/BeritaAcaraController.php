<?php

namespace App\Http\Controllers\Proktor;

use App\Http\Controllers\Controller;
use App\Models\BeritaAcara;
use App\Models\PesertaUjian;
use App\Models\Ujian;
use Illuminate\Http\Request;

class BeritaAcaraController extends Controller
{
    /**
     * Daftar ujian yang bisa diisi berita acara oleh proktor
     */
    public function index()
    {
        $ruangId = auth()->user()->ruang_ujian_id;
        $ruang = auth()->user()->ruangUjian;

        if (!$ruangId) {
            return redirect()->route('proktor.dashboard')
                ->with('error', 'Anda belum ditugaskan ke ruang ujian.');
        }

        $ujianList = Ujian::whereIn('status', ['publish', 'berlangsung', 'selesai'])
            ->whereHas('ruang', fn($q) => $q->where('ruang_ujian.id', $ruangId))
            ->with('mapel')
            ->latest()
            ->get();

        // Load berita acara status per ujian
        $beritaAcaraMap = BeritaAcara::where('ruang_ujian_id', $ruangId)
            ->whereIn('ujian_id', $ujianList->pluck('id'))
            ->pluck('id', 'ujian_id');

        return view('proktor.berita-acara.index', compact('ujianList', 'ruang', 'beritaAcaraMap'));
    }

    /**
     * Form isi berita acara
     */
    public function create(Ujian $ujian)
    {
        $ruangId = auth()->user()->ruang_ujian_id;
        $ruang = auth()->user()->ruangUjian;

        // Cek apakah sudah ada berita acara
        $beritaAcara = BeritaAcara::where('ujian_id', $ujian->id)
            ->where('ruang_ujian_id', $ruangId)
            ->first();

        // Ambil peserta di ruangan ini
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('ruang_ujian_id', $ruangId)
            ->with('siswa.kelas')
            ->get()
            ->sortBy(fn($p) => $p->siswa->nis ?? '');

        $ujian->load('mapel', 'kelas');

        return view('proktor.berita-acara.form', compact('ujian', 'ruang', 'beritaAcara', 'peserta'));
    }

    /**
     * Simpan berita acara
     */
    public function store(Request $request, Ujian $ujian)
    {
        $ruangId = auth()->user()->ruang_ujian_id;

        $request->validate([
            'waktu_mulai' => 'required|string|max:10',
            'waktu_selesai' => 'required|string|max:10',
            'catatan' => 'nullable|string|max:1000',
            'ttd_pengawas' => 'required|string',
            'peserta_tidak_hadir' => 'nullable|array',
            'peserta_tidak_hadir.*' => 'integer|exists:peserta_ujian,id',
        ]);

        BeritaAcara::updateOrCreate(
            [
                'ujian_id' => $ujian->id,
                'ruang_ujian_id' => $ruangId,
            ],
            [
                'proktor_id' => auth()->id(),
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'catatan' => $request->catatan ?: 'Aman',
                'ttd_pengawas' => $request->ttd_pengawas,
                'peserta_tidak_hadir' => $request->peserta_tidak_hadir ?? [],
            ]
        );

        return redirect()->route('proktor.berita-acara.index')
            ->with('success', 'Berita acara berhasil disimpan.');
    }
}
