<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RuangUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProktorController extends Controller
{
    public function index()
    {
        $proktor = User::role('proktor')
            ->with('ruangUjian')
            ->get();
        return view('admin.proktor.index', compact('proktor'));
    }

    public function create()
    {
        $ruang = RuangUjian::where('is_active', true)->get();
        return view('admin.proktor.create', compact('ruang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'ruang_ujian_id' => 'nullable|exists:ruang_ujian,id',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->username . '@proktor.cbt.local',
                'password' => Hash::make($request->password),
                'is_active' => true,
                'ruang_ujian_id' => $request->ruang_ujian_id,
            ]);
            $user->assignRole('proktor');
        });

        return redirect()->route('admin.proktor.index')->with('success', 'Proktor berhasil ditambahkan.');
    }

    public function edit(User $proktor)
    {
        $ruang = RuangUjian::where('is_active', true)->get();
        return view('admin.proktor.edit', compact('proktor', 'ruang'));
    }

    public function update(Request $request, User $proktor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $proktor->id,
            'password' => 'nullable|string|min:6',
            'ruang_ujian_id' => 'nullable|exists:ruang_ujian,id',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'ruang_ujian_id' => $request->ruang_ujian_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $proktor->update($data);

        return redirect()->route('admin.proktor.index')->with('success', 'Proktor berhasil diperbarui.');
    }

    public function destroy(User $proktor)
    {
        $proktor->delete();
        return redirect()->route('admin.proktor.index')->with('success', 'Proktor berhasil dihapus.');
    }
}
