<?php

namespace App\Http\Controllers\Proktor;

use App\Http\Controllers\Controller;
use App\Models\BeritaAcara;
use App\Models\PesertaUjian;
use App\Models\TandaTanganHadir;
use App\Models\Ujian;
use Illuminate\Http\Request;

class DaftarHadirController extends Controller
{
    /**
     * Tampilkan daftar hadir untuk tanda tangan
     */
    public function show(Ujian $ujian)
    {
        $ruangId = auth()->user()->ruang_ujian_id;
        $ruang = auth()->user()->ruangUjian;

        if (!$ruangId) {
            return redirect()->route('proktor.dashboard')
                ->with('error', 'Anda belum ditugaskan ke ruang ujian.');
        }

        // Ambil berita acara untuk ruangan ini
        $beritaAcara = BeritaAcara::where('ujian_id', $ujian->id)
            ->where('ruang_ujian_id', $ruangId)
            ->first();

        if (!$beritaAcara) {
            return redirect()->route('proktor.berita-acara.index')
                ->with('error', 'Isi berita acara terlebih dahulu sebelum mengisi daftar hadir.');
        }

        // Ambil peserta di ruangan ini
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('ruang_ujian_id', $ruangId)
            ->with('siswa.kelas')
            ->get()
            ->sortBy(fn($p) => $p->siswa->nis ?? '');

        // Load tanda tangan yang sudah ada
        $ttdMap = TandaTanganHadir::where('berita_acara_id', $beritaAcara->id)
            ->pluck('tanda_tangan', 'peserta_ujian_id');

        $ujian->load('mapel', 'kelas');

        return view('proktor.daftar-hadir.show', compact('ujian', 'ruang', 'beritaAcara', 'peserta', 'ttdMap'));
    }

    /**
     * Simpan tanda tangan siswa (AJAX)
     */
    public function simpanTtdSiswa(Request $request)
    {
        $request->validate([
            'berita_acara_id' => 'required|exists:berita_acara,id',
            'peserta_ujian_id' => 'required|exists:peserta_ujian,id',
            'tanda_tangan' => 'required|string',
        ]);

        TandaTanganHadir::updateOrCreate(
            [
                'berita_acara_id' => $request->berita_acara_id,
                'peserta_ujian_id' => $request->peserta_ujian_id,
            ],
            [
                'tanda_tangan' => $request->tanda_tangan,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Tanda tangan berhasil disimpan.']);
    }

    /**
     * Simpan tanda tangan pengawas untuk daftar hadir (AJAX)
     */
    public function simpanTtdPengawas(Request $request)
    {
        $request->validate([
            'berita_acara_id' => 'required|exists:berita_acara,id',
            'tanda_tangan' => 'required|string',
        ]);

        $beritaAcara = BeritaAcara::findOrFail($request->berita_acara_id);
        $beritaAcara->update(['ttd_pengawas_hadir' => $request->tanda_tangan]);

        return response()->json(['success' => true, 'message' => 'Tanda tangan pengawas berhasil disimpan.']);
    }
}
