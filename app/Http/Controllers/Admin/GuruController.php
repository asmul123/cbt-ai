<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    public function index()
    {
        $guru = Guru::with(['user', 'mapel'])->get();
        return view('admin.guru.index', compact('guru'));
    }

    public function create()
    {
        $mapel = Mapel::where('is_active', true)->get();
        return view('admin.guru.create', compact('mapel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|max:30|unique:guru',
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'mapel_id' => 'nullable|exists:mapel,id',
            'no_hp' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);
            $user->assignRole('guru');

            Guru::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama' => $request->nama,
                'mapel_id' => $request->mapel_id,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
            ]);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        $mapel = Mapel::where('is_active', true)->get();
        return view('admin.guru.edit', compact('guru', 'mapel'));
    }

    public function update(Request $request, Guru $guru)
    {
        $request->validate([
            'nip' => 'required|string|max:30|unique:guru,nip,' . $guru->id,
            'nama' => 'required|string|max:255',
            'mapel_id' => 'nullable|exists:mapel,id',
            'no_hp' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($request, $guru) {
            $guru->update($request->only(['nip', 'nama', 'mapel_id', 'no_hp', 'alamat']));
            $guru->user->update(['name' => $request->nama]);

            if ($request->filled('password')) {
                $guru->user->update(['password' => Hash::make($request->password)]);
            }
        });

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $guru->user->delete();
        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil dihapus.');
    }
}
