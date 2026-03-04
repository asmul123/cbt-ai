<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\PesertaUjian;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\UjianService;
use Illuminate\Http\Request;

class HasilController extends Controller
{
    protected UjianService $ujianService;

    public function __construct(UjianService $ujianService)
    {
        $this->ujianService = $ujianService;
    }

    /**
     * Daftar ujian yang memiliki hasil
     */
    public function index()
    {
        $ujian = Ujian::whereIn('status', ['berlangsung', 'selesai'])
            ->with('mapel', 'guru')
            ->withCount('peserta')
            ->latest()
            ->get();

        return view('admin.hasil.index', compact('ujian'));
    }

    /**
     * Hasil ujian per ujian
     */
    public function show(Ujian $ujian)
    {
        $hasil = HasilUjian::where('ujian_id', $ujian->id)
            ->with(['siswa.kelas', 'pesertaUjian'])
            ->orderByDesc('nilai_akhir')
            ->get();

        $kelasIds = $ujian->kelas()->pluck('kelas.id');
        $kelas = Kelas::whereIn('id', $kelasIds)->get();

        // Count essay belum dinilai
        $essayBelumDinilai = JawabanSiswa::whereHas('pesertaUjian', fn($q) => $q->where('ujian_id', $ujian->id))
            ->whereHas('soal', fn($q) => $q->where('tipe_soal', 'essay'))
            ->whereNull('skor')
            ->count();

        return view('admin.hasil.show', compact('ujian', 'hasil', 'kelas', 'essayBelumDinilai'));
    }

    /**
     * Detail pengerjaan siswa
     */
    public function detail(Ujian $ujian, Siswa $siswa)
    {
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $hasil = HasilUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $jawaban = JawabanSiswa::where('peserta_ujian_id', $peserta->id)
            ->with(['soal.opsi'])
            ->get()
            ->keyBy('soal_id');

        // Get soal in order
        $soalOrder = $peserta->soal_order ?? $ujian->soal()->pluck('soal.id')->toArray();
        $soalList = \App\Models\Soal::with('opsi')->whereIn('id', $soalOrder)->get()->keyBy('id');

        return view('admin.hasil.detail', compact('ujian', 'siswa', 'peserta', 'hasil', 'jawaban', 'soalOrder', 'soalList'));
    }

    /**
     * Tampilkan soal essay untuk dinilai
     */
    public function nilaiEssay(Ujian $ujian)
    {
        $jawabanEssay = JawabanSiswa::whereHas('pesertaUjian', fn($q) => $q->where('ujian_id', $ujian->id))
            ->whereHas('soal', fn($q) => $q->where('tipe_soal', 'essay'))
            ->with(['soal', 'pesertaUjian.siswa'])
            ->orderByRaw('CASE WHEN skor IS NULL THEN 0 ELSE 1 END')
            ->get();

        return view('admin.hasil.nilaiEssay', compact('ujian', 'jawabanEssay'));
    }

    /**
     * Simpan nilai essay
     */
    public function simpanNilaiEssay(Request $request, JawabanSiswa $jawaban)
    {
        $request->validate(['skor' => 'required|numeric|min:0']);

        $this->ujianService->nilaiEssay($jawaban, $request->skor);

        return back()->with('success', 'Skor essay berhasil disimpan.');
    }
}
