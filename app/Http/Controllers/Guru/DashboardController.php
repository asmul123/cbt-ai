<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\HasilUjian;

class DashboardController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;

        $data = [
            'totalSoal' => Soal::where('guru_id', $guru->id)->count(),
            'totalUjian' => Ujian::where('guru_id', $guru->id)->count(),
            'ujianAktif' => Ujian::where('guru_id', $guru->id)->whereIn('status', ['publish', 'berlangsung'])->count(),
            'ujianTerbaru' => Ujian::where('guru_id', $guru->id)->with('mapel')->latest()->take(5)->get(),
            'soalPerTipe' => Soal::where('guru_id', $guru->id)
                ->selectRaw('tipe_soal, count(*) as total')
                ->groupBy('tipe_soal')
                ->pluck('total', 'tipe_soal'),
        ];

        return view('guru.dashboard', compact('data'));
    }
}
