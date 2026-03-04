<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Ujian;

class VerifyTokenUjian
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token') ?? $request->input('token');

        if (!$token) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Token ujian diperlukan.');
        }

        $ujian = Ujian::where('token_ujian', $token)->first();

        if (!$ujian) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Token ujian tidak valid.');
        }

        if (!$ujian->isAktif()) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Ujian tidak aktif.');
        }

        $request->merge(['ujian' => $ujian]);

        return $next($request);
    }
}
