<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RuangUjian;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class QuickLoginController extends Controller
{
    /**
     * Tampilkan daftar ruang ujian beserta proktor untuk quick login
     */
    public function index()
    {
        $ruangan = RuangUjian::where('is_active', true)
            ->with(['proktor' => fn($q) => $q->where('is_active', true)])
            ->withCount(['pesertaUjian as peserta_aktif_count' => function ($q) {
                $q->where('status', 'mengerjakan');
            }])
            ->orderBy('kode')
            ->get();

        return view('admin.quick-login.index', compact('ruangan'));
    }

    /**
     * Login sebagai proktor tertentu (impersonate)
     */
    public function loginAs(User $user)
    {
        if (!$user->hasRole('proktor')) {
            return back()->with('error', 'User bukan proktor.');
        }

        if (!$user->is_active) {
            return back()->with('error', 'Akun proktor tidak aktif.');
        }

        // Simpan admin ID agar bisa kembali
        Session::put('admin_impersonate_id', Auth::id());

        // Login sebagai proktor
        Auth::login($user);

        return redirect()->route('proktor.dashboard');
    }

    /**
     * Kembali ke akun admin setelah impersonate
     */
    public function kembali()
    {
        $adminId = Session::pull('admin_impersonate_id');

        if (!$adminId) {
            return redirect()->route('login');
        }

        $admin = User::find($adminId);

        if ($admin && $admin->hasRole('admin')) {
            Auth::login($admin);
            return redirect()->route('admin.quick-login.index')->with('success', 'Berhasil kembali ke akun admin.');
        }

        return redirect()->route('login');
    }
}
