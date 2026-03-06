<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RuangUjian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ProktorQuickLoginController extends Controller
{
    /**
     * Tampilkan halaman quick login proktor (public / guest)
     */
    public function index()
    {
        $ruangan = RuangUjian::where('is_active', true)
            ->with(['proktor' => fn($q) => $q->where('is_active', true)])
            ->orderBy('kode')
            ->get();

        return view('auth.proktor-quick-login', compact('ruangan'));
    }

    /**
     * Proses login proktor berdasarkan ruang + password
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string',
        ]);

        // Rate limiting per IP
        $throttleKey = 'proktor-quick-login:' . Str::lower($request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
        }

        $user = User::find($request->user_id);

        if (!$user || !$user->hasRole('proktor') || !$user->is_active) {
            RateLimiter::hit($throttleKey, 60);
            return back()->with('error', 'Akun proktor tidak valid atau tidak aktif.');
        }

        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return back()->with('error', 'Password salah.');
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('proktor.dashboard');
    }
}
