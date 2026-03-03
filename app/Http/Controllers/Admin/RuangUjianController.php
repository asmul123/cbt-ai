<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RuangUjian;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Ujian;
use Illuminate\Http\Request;

class RuangUjianController extends Controller
{
    public function index()
    {
        $ruang = RuangUjian::withCount(['proktor' => function ($q) {
            $q->role('proktor');
        }, 'siswa'])->get();
        return view('admin.ruang.index', compact('ruang'));
    }

    public function create()
    {
        return view('admin.ruang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:ruang_ujian',
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'lokasi' => 'nullable|string|max:255',
        ]);

        RuangUjian::create($request->all());
        return redirect()->route('admin.ruang.index')->with('success', 'Ruang ujian berhasil ditambahkan.');
    }

    public function show(RuangUjian $ruang)
    {
        $ruang->load(['proktor', 'siswa.kelas', 'ujian.mapel']);

        // Ambil kelas yang punya siswa untuk filter
        $kelas = Kelas::whereHas('siswa')->with('jurusan')->where('is_active', true)->get();

        // Ujian yang bisa ditambahkan (belum terdaftar di ruang ini)
        $ujianAvailable = Ujian::whereNotIn('id', $ruang->ujian->pluck('id'))
            ->whereIn('status', ['draft', 'publish', 'berlangsung'])
            ->with('mapel')
            ->latest()
            ->get();

        return view('admin.ruang.show', compact('ruang', 'kelas', 'ujianAvailable'));
    }

    public function edit(RuangUjian $ruang)
    {
        return view('admin.ruang.edit', compact('ruang'));
    }

    public function update(Request $request, RuangUjian $ruang)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:ruang_ujian,kode,' . $ruang->id,
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'lokasi' => 'nullable|string|max:255',
        ]);

        $ruang->update($request->all());
        return redirect()->route('admin.ruang.index')->with('success', 'Ruang ujian berhasil diperbarui.');
    }

    public function destroy(RuangUjian $ruang)
    {
        $ruang->delete();
        return redirect()->route('admin.ruang.index')->with('success', 'Ruang ujian berhasil dihapus.');
    }

    /**
     * Tambah siswa ke ruang (by kelas atau individual)
     */
    public function addSiswa(Request $request, RuangUjian $ruang)
    {
        $request->validate([
            'mode' => 'required|in:kelas,individual',
            'kelas_id' => 'required_if:mode,kelas|nullable|exists:kelas,id',
            'siswa_ids' => 'required_if:mode,individual|nullable|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        if ($request->mode === 'kelas') {
            $count = Siswa::where('kelas_id', $request->kelas_id)
                ->whereNull('ruang_ujian_id')
                ->update(['ruang_ujian_id' => $ruang->id]);

            return back()->with('success', "$count siswa dari kelas berhasil ditambahkan ke ruang.");
        }

        // Individual mode
        Siswa::whereIn('id', $request->siswa_ids)
            ->update(['ruang_ujian_id' => $ruang->id]);

        return back()->with('success', count($request->siswa_ids) . ' siswa berhasil ditambahkan ke ruang.');
    }

    /**
     * Hapus siswa dari ruang
     */
    public function removeSiswa(RuangUjian $ruang, Siswa $siswa)
    {
        $siswa->update(['ruang_ujian_id' => null]);
        return back()->with('success', "Siswa {$siswa->nama} berhasil dikeluarkan dari ruang.");
    }

    /**
     * Hapus semua siswa dari ruang
     */
    public function clearSiswa(RuangUjian $ruang)
    {
        $count = Siswa::where('ruang_ujian_id', $ruang->id)->update(['ruang_ujian_id' => null]);
        return back()->with('success', "$count siswa berhasil dikeluarkan dari ruang.");
    }

    /**
     * Tambah jadwal ujian ke ruang
     */
    public function addUjian(Request $request, RuangUjian $ruang)
    {
        $request->validate([
            'ujian_id' => 'required|exists:ujian,id',
        ]);

        $ruang->ujian()->syncWithoutDetaching([$request->ujian_id]);
        return back()->with('success', 'Jadwal ujian berhasil ditambahkan ke ruang.');
    }

    /**
     * Hapus jadwal ujian dari ruang
     */
    public function removeUjian(RuangUjian $ruang, Ujian $ujian)
    {
        $ruang->ujian()->detach($ujian->id);
        return back()->with('success', 'Jadwal ujian berhasil dihapus dari ruang.');
    }

    /**
     * API: get siswa by kelas yang belum punya ruang
     */
    public function siswaByKelas(Request $request)
    {
        $siswa = Siswa::where('kelas_id', $request->kelas_id)
            ->whereNull('ruang_ujian_id')
            ->with('kelas')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'nis' => $s->nis,
                'nama' => $s->nama,
                'kelas' => $s->kelas->nama ?? '-',
            ]);

        return response()->json($siswa);
    }
}
