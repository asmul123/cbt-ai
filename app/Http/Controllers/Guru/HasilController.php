<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\Kelas;
use App\Models\PesertaUjian;
use App\Models\Siswa;
use App\Models\Soal;
use App\Services\UjianService;
use Illuminate\Http\Request;

class HasilController extends Controller
{
    protected UjianService $ujianService;

    public function __construct(UjianService $ujianService)
    {
        $this->ujianService = $ujianService;
    }

    public function index()
    {
        $guru = auth()->user()->guru;
        $ujian = Ujian::where('guru_id', $guru->id)
            ->whereIn('status', ['berlangsung', 'selesai'])
            ->with('mapel')
            ->withCount('hasilUjian')
            ->latest()
            ->get();

        return view('guru.hasil.index', compact('ujian'));
    }

    public function show(Request $request, Ujian $ujian)
    {
        $query = HasilUjian::where('ujian_id', $ujian->id)
            ->with(['siswa.kelas', 'siswa.jurusan'])
            ->orderByDesc('nilai_akhir');

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $hasil = $query->get();

        $kelasIds = $ujian->kelas()->pluck('kelas.id');
        $kelas = Kelas::whereIn('id', $kelasIds)->get();

        return view('guru.hasil.show', compact('ujian', 'hasil', 'kelas'));
    }

    /**
     * Detail pengerjaan per siswa
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

        $soalOrder = $peserta->soal_order ?? $ujian->soal()->pluck('soal.id')->toArray();
        $soalList = Soal::with('opsi')->whereIn('id', $soalOrder)->get()->keyBy('id');

        return view('guru.hasil.detail', compact('ujian', 'siswa', 'peserta', 'hasil', 'jawaban', 'soalOrder', 'soalList'));
    }

    public function nilaiEssay(Ujian $ujian)
    {
        $jawabanEssay = JawabanSiswa::whereHas('pesertaUjian', function ($q) use ($ujian) {
            $q->where('ujian_id', $ujian->id);
        })
        ->whereHas('soal', function ($q) {
            $q->where('tipe_soal', 'essay');
        })
        ->with(['soal', 'pesertaUjian.siswa'])
        ->orderByRaw('CASE WHEN skor IS NULL THEN 0 ELSE 1 END')
        ->get();

        return view('guru.hasil.nilaiEssay', compact('ujian', 'jawabanEssay'));
    }

    public function simpanNilaiEssay(Request $request, JawabanSiswa $jawaban)
    {
        $request->validate(['skor' => 'required|numeric|min:0']);

        $this->ujianService->nilaiEssay($jawaban, $request->skor);

        return back()->with('success', 'Nilai essay berhasil disimpan.');
    }
}
