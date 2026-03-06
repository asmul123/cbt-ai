<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapel = Mapel::with('jurusan')->withCount('soal')->get();
        return view('admin.mapel.index', compact('mapel'));
    }

    public function create()
    {
        $jurusan = cached_jurusan_aktif();
        return view('admin.mapel.create', compact('jurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:mapel',
            'nama' => 'required|string|max:255',
            'jurusan_id' => 'nullable|exists:jurusan,id',
        ]);

        Mapel::create($request->all());
        forget_master_cache();
        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel)
    {
        $jurusan = cached_jurusan_aktif();
        return view('admin.mapel.edit', compact('mapel', 'jurusan'));
    }

    public function update(Request $request, Mapel $mapel)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:mapel,kode,' . $mapel->id,
            'nama' => 'required|string|max:255',
            'jurusan_id' => 'nullable|exists:jurusan,id',
        ]);

        $mapel->update($request->all());
        forget_master_cache();
        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel)
    {
        $mapel->delete();
        forget_master_cache();
        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
