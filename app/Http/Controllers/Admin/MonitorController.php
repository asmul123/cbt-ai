<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\PesertaUjian;
use App\Models\RuangUjian;
use App\Models\LogAktivitas;
use App\Services\UjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    /**
     * Daftar ujian untuk monitoring
     */
    public function index()
    {
        $ujianList = Ujian::whereIn('status', ['publish', 'berlangsung', 'selesai'])
            ->with('mapel')
            ->withCount('peserta')
            ->latest()
            ->get();

        $ruangList = RuangUjian::where('is_active', true)->get();

        return view('admin.monitor.index', compact('ujianList', 'ruangList'));
    }

    /**
     * Monitor detail ujian — semua ruangan atau filter per ruang
     */
    public function show(Request $request, Ujian $ujian)
    {
        $ruangFilter = $request->get('ruang');
        $ruangList = RuangUjian::where('is_active', true)->get();
        $currentRuang = $ruangFilter ? RuangUjian::find($ruangFilter) : null;

        $ujian->loadCount('soal');

        $query = PesertaUjian::where('ujian_id', $ujian->id)
            ->with(['siswa.kelas', 'siswa.user', 'jawabanSiswa', 'ruangUjian']);

        if ($ruangFilter) {
            if ($ruangFilter === 'none') {
                $query->whereNull('ruang_ujian_id');
            } else {
                $query->where('ruang_ujian_id', $ruangFilter);
            }
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

        return view('admin.monitor.show', compact('ujian', 'peserta', 'stats', 'log', 'ruangList', 'ruangFilter', 'currentRuang'));
    }

    /**
     * Real-time data via AJAX polling
     */
    public function data(Request $request, Ujian $ujian)
    {
        $ruangFilter = $request->get('ruang');

        $query = PesertaUjian::where('ujian_id', $ujian->id)
            ->with(['siswa.kelas', 'ruangUjian']);

        if ($ruangFilter) {
            if ($ruangFilter === 'none') {
                $query->whereNull('ruang_ujian_id');
            } else {
                $query->where('ruang_ujian_id', $ruangFilter);
            }
        }

        $peserta = $query->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nama' => $p->siswa->nama,
                    'kelas' => $p->siswa->kelas->nama ?? '-',
                    'ruang' => $p->ruangUjian->nama ?? '-',
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
            'total' => $peserta->count(),
        ];

        return response()->json(['peserta' => $peserta, 'stats' => $stats]);
    }

    /**
     * Buka kembali ujian siswa
     */
    public function bukaPeserta(Ujian $ujian, PesertaUjian $peserta)
    {
        if ($peserta->ujian_id !== $ujian->id) abort(403);

        DB::transaction(function () use ($peserta) {
            $peserta->hasilUjian()?->delete();

            $pelanggaran = $peserta->jumlah_pelanggaran ?? 0;
            if ($pelanggaran >= 5) $pelanggaran--;

            $peserta->update([
                'status' => 'mengerjakan',
                'waktu_selesai' => null,
                'jumlah_pelanggaran' => $pelanggaran,
            ]);

            LogAktivitas::log(
                auth()->id(),
                'buka_peserta',
                $peserta->ujian_id,
                'Admin membuka kembali ujian siswa: ' . ($peserta->siswa->user->name ?? '-')
            );
        });

        return back()->with('success', 'Ujian siswa dibuka kembali.');
    }

    /**
     * Hapus pengerjaan siswa
     */
    public function hapusPeserta(Ujian $ujian, PesertaUjian $peserta)
    {
        if ($peserta->ujian_id !== $ujian->id) abort(403);

        DB::transaction(function () use ($peserta) {
            $peserta->hasilUjian()?->delete();
            $peserta->jawabanSiswa()->delete();

            $peserta->update([
                'status' => 'belum_mulai',
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'ip_address' => null,
                'jumlah_pelanggaran' => 0,
            ]);

            LogAktivitas::log(
                auth()->id(),
                'hapus_peserta',
                $peserta->ujian_id,
                'Admin menghapus pengerjaan siswa: ' . ($peserta->siswa->user->name ?? '-')
            );
        });

        return back()->with('success', 'Pengerjaan siswa berhasil dihapus.');
    }

    /**
     * Reset pekerjaan siswa
     */
    public function resetPeserta(Ujian $ujian, PesertaUjian $peserta)
    {
        if ($peserta->ujian_id !== $ujian->id) abort(403);

        DB::transaction(function () use ($peserta) {
            $peserta->hasilUjian()?->delete();

            $peserta->update([
                'status' => 'mengerjakan',
                'waktu_mulai' => now(),
                'waktu_selesai' => null,
                'jumlah_pelanggaran' => 0,
            ]);

            LogAktivitas::log(
                auth()->id(),
                'reset_peserta',
                $peserta->ujian_id,
                'Admin mereset pekerjaan siswa: ' . ($peserta->siswa->user->name ?? '-')
            );
        });

        return back()->with('success', 'Pekerjaan siswa berhasil direset.');
    }

    /**
     * Paksa selesaikan ujian siswa
     */
    public function selesaikanPeserta(Ujian $ujian, PesertaUjian $peserta)
    {
        if ($peserta->ujian_id !== $ujian->id) abort(403);

        if ($peserta->status !== 'mengerjakan') {
            return back()->with('error', 'Siswa tidak sedang mengerjakan ujian.');
        }

        $ujianService = app(UjianService::class);
        $hasil = $ujianService->submitUjian($peserta);

        LogAktivitas::log(
            auth()->id(),
            'selesaikan_peserta',
            $peserta->ujian_id,
            'Admin memaksa selesaikan ujian siswa: ' . ($peserta->siswa->user->name ?? '-') . '. Nilai: ' . $hasil->nilai_akhir
        );

        return back()->with('success', 'Ujian siswa diselesaikan. Nilai: ' . $hasil->nilai_akhir);
    }
}
