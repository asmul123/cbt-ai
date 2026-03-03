<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Ujian;
use App\Models\HasilUjian;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalSiswa' => Siswa::count(),
            'totalGuru' => Guru::count(),
            'totalUjian' => Ujian::count(),
            'totalUjianAktif' => Ujian::whereIn('status', ['publish', 'berlangsung'])->count(),
            'totalJurusan' => Jurusan::count(),
            'totalKelas' => Kelas::count(),
            'totalMapel' => Mapel::count(),
            'ujianTerbaru' => Ujian::with('mapel')->latest()->take(5)->get(),
            'nilaiRataRata' => HasilUjian::avg('nilai_akhir') ?? 0,
        ];

        return view('admin.dashboard', compact('data'));
    }
}
