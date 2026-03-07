<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\BeritaAcara;
use App\Models\PesertaUjian;
use App\Models\TandaTanganHadir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DaftarHadirController extends Controller
{
    private function getSiswa(): \App\Models\Siswa
    {
        return Cache::remember(
            'siswa_profile:' . auth()->id(),
            now()->addMinutes(30),
            fn () => auth()->user()->siswa
        );
    }

    /**
     * Tampilkan daftar ujian yang sudah ada berita acaranya untuk TTD
     */
    public function index()
    {
        $siswa = $this->getSiswa();

        // Ambil semua peserta_ujian untuk siswa ini
        $pesertaUjians = PesertaUjian::where('siswa_id', $siswa->id)
            ->whereNotNull('ruang_ujian_id')
            ->with(['ujian.mapel', 'ruangUjian'])
            ->get();

        // Ambil berita acara yang sudah diisi untuk ujian + ruangan peserta
        $items = [];
        foreach ($pesertaUjians as $pu) {
            $ba = BeritaAcara::where('ujian_id', $pu->ujian_id)
                ->where('ruang_ujian_id', $pu->ruang_ujian_id)
                ->first();

            if (!$ba) continue;

            // Cek apakah siswa ini tidak hadir
            $tidakHadir = in_array($pu->id, $ba->peserta_tidak_hadir ?? []);

            // Cek apakah sudah TTD
            $ttd = TandaTanganHadir::where('berita_acara_id', $ba->id)
                ->where('peserta_ujian_id', $pu->id)
                ->first();

            $items[] = (object) [
                'peserta_ujian' => $pu,
                'ujian' => $pu->ujian,
                'ruang' => $pu->ruangUjian,
                'berita_acara' => $ba,
                'tidak_hadir' => $tidakHadir,
                'sudah_ttd' => $ttd ? true : false,
                'tanda_tangan' => $ttd->tanda_tangan ?? null,
            ];
        }

        return view('siswa.daftar-hadir.index', compact('items'));
    }

    /**
     * Simpan tanda tangan siswa (AJAX)
     */
    public function simpanTtd(Request $request)
    {
        $request->validate([
            'berita_acara_id' => 'required|exists:berita_acara,id',
            'tanda_tangan' => 'required|string',
        ]);

        $siswa = $this->getSiswa();

        // Cari peserta_ujian berdasarkan berita_acara
        $ba = BeritaAcara::findOrFail($request->berita_acara_id);

        $pesertaUjian = PesertaUjian::where('ujian_id', $ba->ujian_id)
            ->where('ruang_ujian_id', $ba->ruang_ujian_id)
            ->where('siswa_id', $siswa->id)
            ->first();

        if (!$pesertaUjian) {
            return response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan.'], 403);
        }

        // Cek apakah siswa tidak hadir
        if (in_array($pesertaUjian->id, $ba->peserta_tidak_hadir ?? [])) {
            return response()->json(['success' => false, 'message' => 'Anda tercatat tidak hadir pada ujian ini.'], 403);
        }

        TandaTanganHadir::updateOrCreate(
            [
                'berita_acara_id' => $ba->id,
                'peserta_ujian_id' => $pesertaUjian->id,
            ],
            [
                'tanda_tangan' => $request->tanda_tangan,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Tanda tangan berhasil disimpan.']);
    }
}
