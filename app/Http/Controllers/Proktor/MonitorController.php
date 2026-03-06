<?php

namespace App\Http\Controllers\Proktor;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\PesertaUjian;
use App\Models\LogAktivitas;
use App\Services\UjianService;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    public function dashboard()
    {
        $ruangId = auth()->user()->ruang_ujian_id;
        $ruang = auth()->user()->ruangUjian;

        $query = Ujian::whereIn('status', ['publish', 'berlangsung'])
            ->with('mapel');

        if ($ruangId) {
            $query->whereHas('ruang', fn($q) => $q->where('ruang_ujian.id', $ruangId));
        }

        $ujianAktif = $query->withCount(['peserta' => function ($q) use ($ruangId) {
            if ($ruangId) {
                $q->where('ruang_ujian_id', $ruangId);
            }
        }])->get();

        return view('proktor.dashboard', compact('ujianAktif', 'ruang'));
    }

    public function index()
    {
        $ruangId = auth()->user()->ruang_ujian_id;
        $ruang = auth()->user()->ruangUjian;

        $query = Ujian::whereIn('status', ['publish', 'berlangsung', 'selesai'])
            ->with('mapel');

        if ($ruangId) {
            $query->whereHas('ruang', fn($q) => $q->where('ruang_ujian.id', $ruangId));
        }

        $ujianList = $query->latest()->get();

        return view('proktor.monitor.index', compact('ujianList', 'ruang'));
    }

    public function show(Ujian $ujian)
    {
        $ruangId = auth()->user()->ruang_ujian_id;
        $ruang = auth()->user()->ruangUjian;

        $ujian->loadCount('soal');

        $query = PesertaUjian::where('ujian_id', $ujian->id)
            ->with(['siswa.kelas', 'siswa.user', 'jawabanSiswa', 'ruangUjian']);

        if ($ruangId) {
            $query->where('ruang_ujian_id', $ruangId);
        }

        $peserta = $query->get();

        $stats = [
            'belum_mulai' => $peserta->where('status', 'belum_mulai')->count(),
            'mengerjakan' => $peserta->where('status', 'mengerjakan')->count(),
            'selesai' => $peserta->where('status', 'selesai')->count(),
            'total' => $peserta->count(),
        ];

        $log = LogAktivitas::where('ujian_id', $ujian->id)
            ->with('user')
            ->latest()
            ->take(50)
            ->get();

        return view('proktor.monitor.show', compact('ujian', 'peserta', 'stats', 'log', 'ruang'));
    }

    /**
     * Real-time data via AJAX polling
     */
    public function data(Ujian $ujian)
    {
        $ruangId = auth()->user()->ruang_ujian_id;

        $query = PesertaUjian::where('ujian_id', $ujian->id)
            ->with(['siswa.kelas']);

        if ($ruangId) {
            $query->where('ruang_ujian_id', $ruangId);
        }

        $peserta = $query->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nama' => $p->siswa->nama,
                    'kelas' => $p->siswa->kelas->nama,
                    'status' => $p->status,
                    'waktu_mulai' => $p->waktu_mulai?->format('H:i:s'),
                    'sisa_waktu' => $p->isMengerjakan() ? gmdate('H:i:s', $p->sisaWaktu()) : '-',
                    'ip_address' => $p->ip_address,
                    'jumlah_pelanggaran' => $p->jumlah_pelanggaran ?? 0,
                ];
            });

        $stats = [
            'belum_mulai' => $peserta->where('status', 'belum_mulai')->count(),
            'mengerjakan' => $peserta->where('status', 'mengerjakan')->count(),
            'selesai' => $peserta->where('status', 'selesai')->count(),
        ];

        return response()->json(['peserta' => $peserta, 'stats' => $stats]);
    }

    /**
     * Buka kembali ujian siswa: status kembali ke mengerjakan, waktu dilanjutkan, jawaban tetap
     */
    public function bukaPeserta(Ujian $ujian, PesertaUjian $peserta)
    {
        if ($peserta->ujian_id !== $ujian->id) {
            abort(403);
        }

        DB::transaction(function () use ($peserta) {
            // Hapus hasil ujian (akan dihitung ulang saat submit)
            $peserta->hasilUjian()?->delete();

            // Kurangi pelanggaran 1 jika sudah mencapai maksimal (5)
            $pelanggaran = $peserta->jumlah_pelanggaran ?? 0;
            if ($pelanggaran >= 5) {
                $pelanggaran = $pelanggaran - 1;
            }

            // Buka kembali: status mengerjakan, waktu TIDAK direset (dilanjutkan)
            $peserta->update([
                'status' => 'mengerjakan',
                'waktu_selesai' => null,
                'jumlah_pelanggaran' => $pelanggaran,
            ]);

            // Log aktivitas
            LogAktivitas::log(
                auth()->id(),
                'buka_peserta',
                $peserta->ujian_id,
                'Proktor membuka kembali ujian siswa: ' . ($peserta->siswa->user->name ?? '-')
            );
        });

        return back()->with('success', 'Ujian siswa dibuka kembali. Siswa dapat melanjutkan mengerjakan.');
    }

    /**
     * Hapus pengerjaan siswa: semua jawaban dihapus, status kembali belum_mulai
     */
    public function hapusPeserta(Ujian $ujian, PesertaUjian $peserta)
    {
        if ($peserta->ujian_id !== $ujian->id) {
            abort(403);
        }

        DB::transaction(function () use ($peserta) {
            // Hapus hasil ujian
            $peserta->hasilUjian()?->delete();

            // Hapus semua jawaban siswa
            $peserta->jawabanSiswa()->delete();

            // Reset ke belum mulai
            $peserta->update([
                'status' => 'belum_mulai',
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'ip_address' => null,
                'jumlah_pelanggaran' => 0,
            ]);

            // Log aktivitas
            LogAktivitas::log(
                auth()->id(),
                'hapus_peserta',
                $peserta->ujian_id,
                'Proktor menghapus pengerjaan siswa: ' . ($peserta->siswa->user->name ?? '-')
            );
        });

        return back()->with('success', 'Pengerjaan siswa berhasil dihapus. Siswa dianggap belum mengerjakan.');
    }

    /**
     * Reset pekerjaan siswa: status mengerjakan, waktu direset ulang, jawaban tetap, pelanggaran 0
     */
    public function resetPeserta(Ujian $ujian, PesertaUjian $peserta)
    {
        if ($peserta->ujian_id !== $ujian->id) {
            abort(403);
        }

        DB::transaction(function () use ($peserta) {
            // Hapus hasil ujian (akan dihitung ulang saat submit)
            $peserta->hasilUjian()?->delete();

            // Reset status ke mengerjakan dengan waktu baru, pelanggaran direset
            $peserta->update([
                'status' => 'mengerjakan',
                'waktu_mulai' => now(),
                'waktu_selesai' => null,
                'jumlah_pelanggaran' => 0,
            ]);

            // Log aktivitas
            LogAktivitas::log(
                auth()->id(),
                'reset_peserta',
                $peserta->ujian_id,
                'Proktor mereset pekerjaan siswa: ' . ($peserta->siswa->user->name ?? '-')
            );
        });

        return back()->with('success', 'Pekerjaan siswa berhasil direset. Waktu dimulai ulang, jawaban tetap ada.');
    }

    /**
     * Paksa selesaikan ujian siswa dan hitung skor
     */
    public function selesaikanPeserta(Ujian $ujian, PesertaUjian $peserta)
    {
        if ($peserta->ujian_id !== $ujian->id) {
            abort(403);
        }

        if ($peserta->status !== 'mengerjakan') {
            return back()->with('error', 'Siswa tidak sedang mengerjakan ujian.');
        }

        $ujianService = app(UjianService::class);
        $hasil = $ujianService->submitUjian($peserta);

        // Log aktivitas
        LogAktivitas::log(
            auth()->id(),
            'selesaikan_peserta',
            $peserta->ujian_id,
            'Proktor memaksa selesaikan ujian siswa: ' . ($peserta->siswa->user->name ?? '-') . '. Nilai: ' . $hasil->nilai_akhir
        );

        return back()->with('success', 'Ujian siswa berhasil diselesaikan. Nilai: ' . $hasil->nilai_akhir);
    }
}
