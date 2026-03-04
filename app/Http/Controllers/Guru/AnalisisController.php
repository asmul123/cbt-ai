<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Soal;
use App\Services\AnalisisService;
use Illuminate\Http\Request;

class AnalisisController extends Controller
{
    protected AnalisisService $analisisService;

    public function __construct(AnalisisService $analisisService)
    {
        $this->analisisService = $analisisService;
    }

    public function index()
    {
        $guru = auth()->user()->guru;
        $ujian = Ujian::where('guru_id', $guru->id)
            ->where('status', 'selesai')
            ->with('mapel')
            ->latest()
            ->get();

        return view('guru.analisis.index', compact('ujian'));
    }

    public function show(Ujian $ujian)
    {
        $soalList = $ujian->soal()->with('opsi')->get();
        $analisis = [];

        foreach ($soalList as $soal) {
            $analisis[] = [
                'soal' => $soal,
                'tingkat_kesukaran' => $this->analisisService->tingkatKesukaran($soal, $ujian),
                'daya_pembeda' => $this->analisisService->dayaPembeda($soal, $ujian),
                'distribusi' => $this->analisisService->distribusiJawaban($soal, $ujian),
            ];
        }

        $statistik = $this->analisisService->statistikUjian($ujian);
        $ranking = $this->analisisService->ranking($ujian);

        return view('guru.analisis.show', compact('ujian', 'analisis', 'statistik', 'ranking'));
    }
}
