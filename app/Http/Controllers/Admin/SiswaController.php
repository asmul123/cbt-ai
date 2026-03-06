<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['user', 'kelas', 'jurusan']);

        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->jurusan_id) {
            $query->where('jurusan_id', $request->jurusan_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%");
            });
        }

        $siswa = $query->paginate(25);
        $kelas = cached_kelas_all();
        $jurusan = cached_jurusan_all();

        return view('admin.siswa.index', compact('siswa', 'kelas', 'jurusan'));
    }

    public function create()
    {
        $kelas = cached_kelas_aktif();
        $jurusan = cached_jurusan_aktif();
        return view('admin.siswa.create', compact('kelas', 'jurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|max:20|unique:siswa',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'jurusan_id' => 'required|exists:jurusan,id',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama,
                'username' => $request->nis,
                'email' => $request->nis . '@siswa.cbt.local',
                'password' => Hash::make($request->nis),
                'is_active' => true,
            ]);
            $user->assignRole('siswa');

            Siswa::create([
                'user_id' => $user->id,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'kelas_id' => $request->kelas_id,
                'jurusan_id' => $request->jurusan_id,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
            ]);
        });

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        $kelas = cached_kelas_aktif();
        $jurusan = cached_jurusan_aktif();
        return view('admin.siswa.edit', compact('siswa', 'kelas', 'jurusan'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nis' => 'required|string|max:20|unique:siswa,nis,' . $siswa->id,
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'jurusan_id' => 'required|exists:jurusan,id',
        ]);

        DB::transaction(function () use ($request, $siswa) {
            $siswa->update($request->only(['nis', 'nisn', 'nama', 'jenis_kelamin', 'kelas_id', 'jurusan_id', 'no_hp', 'alamat']));
            $siswa->user->update(['name' => $request->nama]);

            if ($request->filled('password')) {
                $siswa->user->update(['password' => Hash::make($request->password)]);
            }
        });

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->user->delete();
        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }

    public function resetPassword(Siswa $siswa)
    {
        $siswa->user->update(['password' => Hash::make($siswa->nis)]);
        return back()->with('success', "Password siswa {$siswa->nama} berhasil direset ke NIS.");
    }
}
