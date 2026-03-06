<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Models\Soal;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\PesertaUjian;
use App\Models\RuangUjian;
use Illuminate\Http\Request;

class UjianAdminController extends Controller
{
    public function index()
    {
        $ujian = Ujian::with(['mapel', 'guru', 'kelas', 'ruang'])
            ->withCount(['soal', 'peserta'])
            ->latest()
            ->get();
        return view('admin.ujian.index', compact('ujian'));
    }

    public function create(Request $request)
    {
        $mapel = cached_mapel_aktif();
        $kelas = cached_kelas_aktif();
        $ruang = RuangUjian::where('is_active', true)->withCount('siswa')->get();
        $guru = Guru::with('user')->get();

        // Pre-select ruang jika dari halaman ruang
        $preselectedRuang = $request->get('ruang_id');

        return view('admin.ujian.create', compact('mapel', 'kelas', 'ruang', 'guru', 'preselectedRuang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ujian' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => 'nullable|exists:guru,id',
            'durasi' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'ruang_ids' => 'nullable|array',
            'ruang_ids.*' => 'exists:ruang_ujian,id',
            'kkm' => 'required|numeric|min:0|max:100',
        ]);

        // Jika massal dicentang, ambil semua ruang aktif
        $ruangIds = $request->ruang_ids ?? [];
        if ($request->boolean('massal')) {
            $ruangIds = RuangUjian::where('is_active', true)->pluck('id')->toArray();
        }

        $ujian = Ujian::create([
            'nama_ujian' => $request->nama_ujian,
            'mapel_id' => $request->mapel_id,
            'guru_id' => $request->guru_id,
            'durasi' => $request->durasi,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'token_ujian' => Ujian::generateToken(),
            'status' => 'draft',
            'acak_soal' => $request->boolean('acak_soal'),
            'acak_opsi' => $request->boolean('acak_opsi'),
            'batasi_ip' => $request->boolean('batasi_ip'),
            'ip_allowed' => $request->ip_allowed,
            'fullscreen_mode' => $request->boolean('fullscreen_mode', true),
            'tampilkan_nilai' => $request->boolean('tampilkan_nilai'),
            'jumlah_soal_tampil' => $request->jumlah_soal_tampil,
            'kkm' => $request->kkm,
            'keterangan' => $request->keterangan,
        ]);

        $ujian->kelas()->sync($request->kelas_ids);
        if (!empty($ruangIds)) {
            $ujian->ruang()->sync($ruangIds);
        }

        return redirect()->route('admin.ujian.soal', $ujian)->with('success', 'Ujian berhasil dibuat. Silakan pilih soal.');
    }

    public function edit(Ujian $ujian)
    {
        $mapel = cached_mapel_aktif();
        $kelas = cached_kelas_aktif();
        $ruang = RuangUjian::where('is_active', true)->withCount('siswa')->get();
        $guru = Guru::with('user')->get();
        $ujian->load('kelas', 'ruang');

        return view('admin.ujian.edit', compact('ujian', 'mapel', 'kelas', 'ruang', 'guru'));
    }

    public function update(Request $request, Ujian $ujian)
    {
        $request->validate([
            'nama_ujian' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => 'nullable|exists:guru,id',
            'durasi' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'ruang_ids' => 'nullable|array',
            'ruang_ids.*' => 'exists:ruang_ujian,id',
            'kkm' => 'required|numeric|min:0|max:100',
        ]);

        $ruangIds = $request->ruang_ids ?? [];
        if ($request->boolean('massal')) {
            $ruangIds = RuangUjian::where('is_active', true)->pluck('id')->toArray();
        }

        $ujian->update([
            'nama_ujian' => $request->nama_ujian,
            'mapel_id' => $request->mapel_id,
            'guru_id' => $request->guru_id,
            'durasi' => $request->durasi,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'acak_soal' => $request->boolean('acak_soal'),
            'acak_opsi' => $request->boolean('acak_opsi'),
            'batasi_ip' => $request->boolean('batasi_ip'),
            'ip_allowed' => $request->ip_allowed,
            'fullscreen_mode' => $request->boolean('fullscreen_mode'),
            'tampilkan_nilai' => $request->boolean('tampilkan_nilai'),
            'jumlah_soal_tampil' => $request->jumlah_soal_tampil,
            'kkm' => $request->kkm,
            'keterangan' => $request->keterangan,
        ]);

        $ujian->kelas()->sync($request->kelas_ids);
        $ujian->ruang()->sync($ruangIds);

        return redirect()->route('admin.ujian.index')->with('success', 'Ujian berhasil diperbarui.');
    }

    /**
     * Pilih soal untuk ujian
     */
    public function soal(Ujian $ujian)
    {
        $ujian->load('soal');
        $soalTersedia = Soal::where('mapel_id', $ujian->mapel_id)
            ->where('status', 'aktif')
            ->with('opsi')
            ->get();

        return view('admin.ujian.soal', compact('ujian', 'soalTersedia'));
    }

    public function soalSync(Request $request, Ujian $ujian)
    {
        $request->validate([
            'soal_ids' => 'required|array|min:1',
            'soal_ids.*' => 'exists:soal,id',
        ]);

        $syncData = [];
        foreach ($request->soal_ids as $i => $soalId) {
            $syncData[$soalId] = ['urutan' => $i];
        }

        $ujian->soal()->sync($syncData);

        return redirect()->route('admin.ujian.index')->with('success', 'Soal ujian berhasil disimpan. Total: ' . count($request->soal_ids) . ' soal.');
    }

    /**
     * Publish ujian dan generate peserta
     */
    public function publish(Ujian $ujian)
    {
        if ($ujian->soal()->count() === 0) {
            return back()->with('error', 'Tidak bisa publish ujian tanpa soal.');
        }

        $ujian->update(['status' => 'publish']);
        $this->generatePeserta($ujian);

        return back()->with('success', "Ujian berhasil dipublish. Token: {$ujian->token_ujian}");
    }

    /**
     * Generate peserta ujian dari kelas terdaftar, ruang dari assignment admin siswa
     */
    protected function generatePeserta(Ujian $ujian)
    {
        $kelasIds = $ujian->kelas()->pluck('kelas.id')->toArray();
        $siswaList = Siswa::whereIn('kelas_id', $kelasIds)->get();

        foreach ($siswaList as $siswa) {
            PesertaUjian::firstOrCreate(
                ['ujian_id' => $ujian->id, 'siswa_id' => $siswa->id],
                [
                    'status' => 'belum_mulai',
                    'ruang_ujian_id' => $siswa->ruang_ujian_id,
                ]
            );
        }
    }

    public function generateToken(Ujian $ujian)
    {
        $ujian->update(['token_ujian' => Ujian::generateToken()]);
        return back()->with('success', "Token ujian berhasil digenerate: {$ujian->token_ujian}");
    }

    public function updateStatus(Request $request, Ujian $ujian)
    {
        $request->validate(['status' => 'required|in:draft,publish,berlangsung,selesai']);
        $ujian->update(['status' => $request->status]);
        return back()->with('success', "Status ujian berhasil diubah ke {$request->status}.");
    }

    public function destroy(Ujian $ujian)
    {
        $ujian->delete();
        return redirect()->route('admin.ujian.index')->with('success', 'Ujian berhasil dihapus.');
    }
}
