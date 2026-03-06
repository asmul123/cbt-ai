<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\PesertaUjian;
use App\Models\HasilUjian;
use App\Models\LogAktivitas;
use App\Services\UjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiswaController extends Controller
{
    protected UjianService $ujianService;

    public function __construct(UjianService $ujianService)
    {
        $this->ujianService = $ujianService;
    }

    public function dashboard()
    {
        $siswa = auth()->user()->siswa;
        $kelasId = $siswa->kelas_id;

        $ujianTersedia = Ujian::whereIn('status', ['publish', 'berlangsung'])
            ->whereHas('kelas', fn($q) => $q->where('kelas.id', $kelasId))
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->with('mapel')
            ->get();

        $riwayat = HasilUjian::where('siswa_id', $siswa->id)
            ->with(['ujian.mapel'])
            ->latest()
            ->take(5)
            ->get();

        return view('siswa.dashboard', compact('ujianTersedia', 'riwayat', 'siswa'));
    }

    public function ujianIndex()
    {
        $siswa = auth()->user()->siswa;

        $ujian = Ujian::whereIn('status', ['publish', 'berlangsung'])
            ->whereHas('kelas', fn($q) => $q->where('kelas.id', $siswa->kelas_id))
            ->with('mapel')
            ->withCount('soal')
            ->get();

        // Mark which ones are already taken
        $pesertaStatus = PesertaUjian::where('siswa_id', $siswa->id)
            ->pluck('status', 'ujian_id');

        return view('siswa.ujian.index', compact('ujian', 'pesertaStatus', 'siswa'));
    }

    public function masukToken()
    {
        return view('siswa.ujian.token');
    }

    public function verifikasiToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $ujian = Ujian::where('token_ujian', strtoupper($request->token))->first();

        if (!$ujian) {
            return back()->with('error', 'Token ujian tidak valid.');
        }

        $siswa = auth()->user()->siswa;
        $errors = $this->ujianService->cekAksesUjian($ujian, $siswa, $request->ip());

        if (!empty($errors)) {
            return back()->with('error', implode(' ', $errors));
        }

        return redirect()->route('siswa.ujian.konfirmasi', $ujian);
    }

    public function konfirmasi(Ujian $ujian)
    {
        $siswa = auth()->user()->siswa;
        $ujian->load('mapel');
        $ujian->loadCount('soal');

        // Check if already started
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return view('siswa.ujian.konfirmasi', compact('ujian', 'siswa', 'peserta'));
    }

    public function mulaiUjian(Ujian $ujian)
    {
        $siswa = auth()->user()->siswa;

        $errors = $this->ujianService->cekAksesUjian($ujian, $siswa, request()->ip());
        if (!empty($errors)) {
            return redirect()->route('siswa.dashboard')->with('error', implode(' ', $errors));
        }

        // Check if already started (resume)
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        if (!$peserta || $peserta->isBelumMulai()) {
            $peserta = $this->ujianService->mulaiUjian($ujian, $siswa, request()->ip());
        }

        if ($peserta->isSelesai()) {
            return redirect()->route('siswa.dashboard')->with('error', 'Anda sudah menyelesaikan ujian ini.');
        }

        return redirect()->route('siswa.ujian.kerjakan', ['ujian' => $ujian, 'nomor' => 1]);
    }

    public function kerjakan(Ujian $ujian, int $nomor = 1)
    {
        $siswa = auth()->user()->siswa;
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $peserta->setRelation('ujian', $ujian);

        if ($peserta->isSelesai()) {
            return redirect()->route('siswa.dashboard')->with('error', 'Ujian sudah selesai.');
        }

        // Check time
        if ($peserta->sisaWaktu() <= 0) {
            $this->ujianService->submitUjian($peserta);
            return redirect()->route('siswa.ujian.selesai', $ujian)->with('success', 'Waktu habis! Ujian otomatis disubmit.');
        }

        $soalOrder = $peserta->soal_order ?? $ujian->soal()->pluck('soal.id')->toArray();
        $totalSoal = count($soalOrder);
        $nomor = max(1, min($nomor, $totalSoal));
        $soalId = $soalOrder[$nomor - 1] ?? null;

        if (!$soalId) {
            return redirect()->route('siswa.dashboard')->with('error', 'Soal tidak ditemukan.');
        }

        // Cache semua soal + opsi per peserta (TTL = durasi ujian + 1 jam buffer)
        $cacheKey = "soal_ujian:{$peserta->id}";
        $ttl = now()->addMinutes(($ujian->durasi ?? 90) + 60);
        $semuaSoal = Cache::remember($cacheKey, $ttl, function () use ($soalOrder) {
            return \App\Models\Soal::with('opsi')
                ->whereIn('id', $soalOrder)
                ->get()
                ->keyBy('id');
        });

        $soal = $semuaSoal[$soalId] ?? \App\Models\Soal::with('opsi')->findOrFail($soalId);

        // Get answered status for navigation (harus fresh dari DB)
        $jawabanStatus = $peserta->jawabanSiswa()
            ->select('soal_id', 'jawaban', 'ragu_ragu')
            ->get()
            ->keyBy('soal_id');

        // Get current answer (fresh dari DB)
        $jawaban = $jawabanStatus[$soalId] ?? null;

        // Acak opsi if enabled (deterministic per peserta+soal so order stays consistent)
        $opsiList = $soal->opsi;
        if ($ujian->acak_opsi && in_array($soal->tipe_soal, ['pg', 'pg_kompleks'])) {
            $opsiList = $opsiList->sortBy(function($opsi) use ($peserta, $soalId) {
                return crc32($peserta->id . '-' . $soalId . '-' . $opsi->id);
            })->values();
        }

        return view('siswa.ujian.kerjakan', compact(
            'ujian', 'peserta', 'soal', 'opsiList', 'jawaban',
            'nomor', 'totalSoal', 'soalOrder', 'jawabanStatus'
        ));
    }

    public function simpanJawaban(Request $request, Ujian $ujian)
    {
        $siswa = auth()->user()->siswa;
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $soalId = $request->soal_id;

        // Process jawaban based on soal type
        $jawabanValue = $request->jawaban;
        if (is_array($jawabanValue)) {
            $jawabanValue = json_encode($jawabanValue);
        }

        $this->ujianService->simpanJawaban($peserta, $soalId, $jawabanValue, $request->boolean('ragu_ragu'));

        // Handle navigation
        if ($request->action === 'submit') {
            return redirect()->route('siswa.ujian.submitKonfirmasi', $ujian);
        }

        $currentNomor = (int) $request->nomor;
        if ($request->action === 'next') {
            $nextNomor = $currentNomor + 1;
        } else {
            $nextNomor = $currentNomor;
        }

        return redirect()->route('siswa.ujian.kerjakan', ['ujian' => $ujian, 'nomor' => $nextNomor]);
    }

    public function submitKonfirmasi(Ujian $ujian)
    {
        $siswa = auth()->user()->siswa;
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $soalOrder = $peserta->soal_order ?? [];
        $totalSoal = count($soalOrder);
        $terjawab = $peserta->jawabanSiswa()->whereNotNull('jawaban')->count();
        $raguRagu = $peserta->jawabanSiswa()->where('ragu_ragu', true)->count();
        $belumDijawab = $totalSoal - $terjawab;

        return view('siswa.ujian.submit', compact('ujian', 'peserta', 'totalSoal', 'terjawab', 'raguRagu', 'belumDijawab'));
    }

    public function submitUjian(Ujian $ujian)
    {
        $siswa = auth()->user()->siswa;
        $peserta = PesertaUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $this->ujianService->submitUjian($peserta);

        // Hapus cache soal karena ujian sudah selesai
        Cache::forget("soal_ujian:{$peserta->id}");

        return redirect()->route('siswa.ujian.selesai', $ujian)->with('success', 'Ujian berhasil disubmit!');
    }

    public function selesai(Ujian $ujian)
    {
        $siswa = auth()->user()->siswa;
        $hasil = HasilUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return view('siswa.ujian.selesai', compact('ujian', 'hasil'));
    }

    public function riwayat()
    {
        $siswa = auth()->user()->siswa;
        $hasil = HasilUjian::where('siswa_id', $siswa->id)
            ->with(['ujian.mapel'])
            ->latest()
            ->paginate(20);

        return view('siswa.riwayat', compact('hasil'));
    }

    /**
     * Log anti-cheat activity via AJAX
     */
    public function logAktivitas(Request $request)
    {
        $aktivitas = $request->tipe ?? $request->aktivitas ?? 'unknown';
        $keterangan = $request->detail ?? $request->keterangan;
        $ujianId = $request->ujian_id;

        LogAktivitas::log(
            auth()->id(),
            $aktivitas,
            $ujianId,
            $keterangan
        );

        // Jika pelanggaran serius (tab_switch, window_blur, exit_fullscreen), increment counter
        $isPelanggaran = in_array($aktivitas, ['tab_switch', 'window_blur', 'exit_fullscreen']);
        $jumlahPelanggaran = 0;
        $autoSubmit = false;

        if ($isPelanggaran && $ujianId) {
            $siswa = auth()->user()->siswa;
            $peserta = PesertaUjian::where('ujian_id', $ujianId)
                ->where('siswa_id', $siswa->id)
                ->where('status', 'mengerjakan')
                ->first();

            if ($peserta) {
                $peserta->increment('jumlah_pelanggaran');
                $peserta->refresh();
                $jumlahPelanggaran = $peserta->jumlah_pelanggaran;

                // Batas maksimal pelanggaran = 5, auto submit
                $maxPelanggaran = 5;
                if ($jumlahPelanggaran >= $maxPelanggaran) {
                    $this->ujianService->submitUjian($peserta);
                    $autoSubmit = true;
                }
            }
        }

        return response()->json([
            'status' => 'ok',
            'jumlah_pelanggaran' => $jumlahPelanggaran,
            'auto_submit' => $autoSubmit,
        ]);
    }
}
