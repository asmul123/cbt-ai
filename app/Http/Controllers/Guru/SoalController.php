<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Mapel;
use App\Services\BankSoalService;
use Illuminate\Http\Request;

class SoalController extends Controller
{
    protected BankSoalService $bankSoalService;

    public function __construct(BankSoalService $bankSoalService)
    {
        $this->bankSoalService = $bankSoalService;
    }

    public function index(Request $request)
    {
        $guru = auth()->user()->guru;
        $query = Soal::where('guru_id', $guru->id)->with(['mapel', 'opsi']);

        if ($request->mapel_id) {
            $query->where('mapel_id', $request->mapel_id);
        }
        if ($request->tipe_soal) {
            $query->where('tipe_soal', $request->tipe_soal);
        }
        if ($request->tingkat) {
            $query->where('tingkat_kesulitan', $request->tingkat);
        }
        if ($request->search) {
            $query->where('soal', 'like', "%{$request->search}%");
        }

        $soal = $query->latest()->paginate(20);
        $mapel = Mapel::where('is_active', true)->get();

        return view('guru.soal.index', compact('soal', 'mapel'));
    }

    public function create()
    {
        $mapel = Mapel::where('is_active', true)->get();
        return view('guru.soal.create', compact('mapel'));
    }

    public function store(Request $request)
    {
        $rules = [
            'mapel_id' => 'required|exists:mapel,id',
            'tipe_soal' => 'required|in:pg,pg_kompleks,isian,essay',
            'soal' => 'required|string',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
            'bobot' => 'required|numeric|min:0.01',
            'gambar' => 'nullable|image|max:2048',
        ];

        if (in_array($request->tipe_soal, ['pg', 'pg_kompleks'])) {
            $rules['opsi'] = 'required|array|min:2';
            $rules['opsi.*.teks'] = 'required|string';
        } elseif ($request->tipe_soal === 'isian') {
            $rules['jawaban_benar'] = 'required|string';
        }

        $request->validate($rules);

        $guru = auth()->user()->guru;

        $soalData = [
            'mapel_id' => $request->mapel_id,
            'guru_id' => $guru->id,
            'tipe_soal' => $request->tipe_soal,
            'soal' => $request->soal,
            'tingkat_kesulitan' => $request->tingkat_kesulitan,
            'kompetensi_dasar' => $request->kompetensi_dasar,
            'bobot' => $request->bobot,
            'pembahasan' => $request->pembahasan,
            'status' => $request->status ?? 'draft',
        ];

        if ($request->hasFile('gambar')) {
            $soalData['gambar'] = $this->bankSoalService->uploadGambar($request->file('gambar'));
        }

        $opsiData = [];
        if (in_array($request->tipe_soal, ['pg', 'pg_kompleks']) && $request->opsi) {
            $jawabanBenar = $request->input('jawaban_benar_pg', []);
            $labelIndex = 0;
            foreach ($request->opsi as $i => $opsi) {
                if (!empty($opsi['teks'])) {
                    $opsiData[] = [
                        'label' => chr(65 + $labelIndex),
                        'teks' => $opsi['teks'],
                        'is_benar' => in_array($i, (array)$jawabanBenar),
                        'urutan' => $labelIndex,
                    ];
                    $labelIndex++;
                }
            }
        } elseif ($request->tipe_soal === 'isian' && $request->jawaban_benar) {
            $opsiData[] = [
                'label' => 'A',
                'teks' => $request->jawaban_benar,
                'is_benar' => true,
                'urutan' => 0,
            ];
        }

        $this->bankSoalService->buatSoal($soalData, $opsiData);

        return redirect()->route('guru.soal.index')->with('success', 'Soal berhasil ditambahkan.');
    }

    public function show(Soal $soal)
    {
        $soal->load('opsi');
        return view('guru.soal.show', compact('soal'));
    }

    public function edit(Soal $soal)
    {
        $soal->load('opsi');
        $mapel = Mapel::where('is_active', true)->get();
        return view('guru.soal.edit', compact('soal', 'mapel'));
    }

    public function update(Request $request, Soal $soal)
    {
        $rules = [
            'mapel_id' => 'required|exists:mapel,id',
            'tipe_soal' => 'required|in:pg,pg_kompleks,isian,essay',
            'soal' => 'required|string',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
            'bobot' => 'required|numeric|min:0.01',
        ];

        if (in_array($request->tipe_soal, ['pg', 'pg_kompleks'])) {
            $rules['opsi'] = 'required|array|min:2';
            $rules['opsi.*.teks'] = 'required|string';
        } elseif ($request->tipe_soal === 'isian') {
            $rules['jawaban_benar'] = 'required|string';
        }

        $request->validate($rules);

        $soalData = $request->only(['mapel_id', 'tipe_soal', 'soal', 'tingkat_kesulitan', 'kompetensi_dasar', 'bobot', 'pembahasan', 'status']);

        if ($request->hasFile('gambar')) {
            $soalData['gambar'] = $this->bankSoalService->uploadGambar($request->file('gambar'));
        }

        $opsiData = [];
        if (in_array($request->tipe_soal, ['pg', 'pg_kompleks']) && $request->opsi) {
            $jawabanBenar = $request->input('jawaban_benar_pg', []);
            $labelIndex = 0;
            foreach ($request->opsi as $i => $opsi) {
                if (!empty($opsi['teks'])) {
                    $opsiData[] = [
                        'label' => chr(65 + $labelIndex),
                        'teks' => $opsi['teks'],
                        'is_benar' => in_array($i, (array)$jawabanBenar),
                        'urutan' => $labelIndex,
                    ];
                    $labelIndex++;
                }
            }
        } elseif ($request->tipe_soal === 'isian' && $request->jawaban_benar) {
            $opsiData[] = [
                'label' => 'A',
                'teks' => $request->jawaban_benar,
                'is_benar' => true,
                'urutan' => 0,
            ];
        }

        $this->bankSoalService->updateSoal($soal, $soalData, $opsiData);

        return redirect()->route('guru.soal.index')->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Soal $soal)
    {
        $soal->delete();
        return redirect()->route('guru.soal.index')->with('success', 'Soal berhasil dihapus.');
    }

    public function duplicate(Soal $soal)
    {
        $this->bankSoalService->duplikatSoal($soal);
        return back()->with('success', 'Soal berhasil diduplikat.');
    }

    public function import()
    {
        $mapel = Mapel::where('is_active', true)->get();
        return view('guru.soal.import', compact('mapel'));
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'mapel_id' => 'required|exists:mapel,id',
        ]);

        $guru = auth()->user()->guru;
        $result = $this->bankSoalService->importFromExcel($request->file('file'), $request->mapel_id, $guru->id);

        $msg = "Import selesai: {$result['success']} berhasil, {$result['failed']} gagal.";
        if (!empty($result['errors'])) {
            $msg .= ' Errors: ' . implode(', ', array_slice($result['errors'], 0, 3));
        }

        return redirect()->route('guru.soal.index')->with('success', $msg);
    }

    /**
     * Upload gambar dari CKEditor (SimpleUploadAdapter)
     */
    public function uploadGambar(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|mimes:jpeg,png,gif,webp,svg|max:5120',
        ]);

        $path = $request->file('upload')->store('soal-images', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Hapus gambar soal (field gambar pada tabel soal)
     */
    public function hapusGambarSoal(Soal $soal)
    {
        if ($soal->gambar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($soal->gambar);
            $soal->update(['gambar' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Gambar soal berhasil dihapus']);
    }

    /**
     * Hapus gambar yang diupload dari CKEditor
     */
    public function hapusGambar(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $url = $request->input('url');

        // Extract relative path from full URL
        // URL format: http://domain/storage/soal-images/filename.ext
        $storageUrl = asset('storage/');
        if (str_starts_with($url, $storageUrl)) {
            $relativePath = str_replace($storageUrl, '', $url);
            $relativePath = ltrim($relativePath, '/');

            // Security: only allow deletion inside soal-images directory
            if (str_starts_with($relativePath, 'soal-images/') && !str_contains($relativePath, '..')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);

                return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan'], 404);
    }
}
