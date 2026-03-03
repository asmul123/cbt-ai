<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Halaman utama import
     */
    public function index()
    {
        return view('admin.import.index');
    }

    /**
     * Import Kelas
     */
    public function importKelas(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $result = $this->importService->importKelas($request->file('file'));

        return back()->with('result', $result)->with('import_type', 'Kelas');
    }

    /**
     * Import Ruang Ujian
     */
    public function importRuang(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $result = $this->importService->importRuang($request->file('file'));

        return back()->with('result', $result)->with('import_type', 'Ruang Ujian');
    }

    /**
     * Import Siswa
     */
    public function importSiswa(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $result = $this->importService->importSiswa($request->file('file'));

        return back()->with('result', $result)->with('import_type', 'Siswa');
    }

    /**
     * Import Guru
     */
    public function importGuru(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $result = $this->importService->importGuru($request->file('file'));

        return back()->with('result', $result)->with('import_type', 'Guru');
    }

    /**
     * Import Distribusi Ruang
     */
    public function importDistribusiRuang(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'mode' => 'required|in:siswa,kelas',
        ]);

        $result = $this->importService->importDistribusiRuang($request->file('file'), $request->mode);

        return back()->with('result', $result)->with('import_type', 'Distribusi Ruang');
    }

    /**
     * Import Proktor
     */
    public function importProktor(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $result = $this->importService->importProktor($request->file('file'));

        return back()->with('result', $result)->with('import_type', 'Proktor');
    }

    // ==================== RESET METHODS ====================

    /**
     * Reset data
     */
    public function reset(Request $request, string $type)
    {
        $validTypes = ['siswa', 'guru', 'proktor', 'ruang', 'kelas'];

        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $result = match ($type) {
            'siswa'   => $this->importService->resetSiswa(),
            'guru'    => $this->importService->resetGuru(),
            'proktor' => $this->importService->resetProktor(),
            'ruang'   => $this->importService->resetRuang(),
            'kelas'   => $this->importService->resetKelas(),
        };

        return back()->with('reset_result', $result);
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate(string $type)
    {
        $templates = [
            'kelas'       => 'template_import_kelas.xlsx',
            'ruang'       => 'template_import_ruang.xlsx',
            'guru'        => 'template_import_guru.xlsx',
            'proktor'     => 'template_import_proktor.xlsx',
            'siswa'       => 'template_import_siswa.xlsx',
            'distribusi'  => 'template_import_distribusi_ruang.xlsx',
        ];

        if (!isset($templates[$type])) {
            abort(404);
        }

        $path = storage_path('app/templates/' . $templates[$type]);

        if (!file_exists($path)) {
            // Generate on-the-fly if template not found
            return $this->generateTemplate($type);
        }

        return response()->download($path, $templates[$type]);
    }

    /**
     * Generate template on the fly using Maatwebsite Excel
     */
    private function generateTemplate(string $type)
    {
        $headers = match ($type) {
            'kelas' => [['Nama Kelas', 'Tingkat (X/XI/XII)', 'Jurusan (kode/nama)', 'Tahun Ajaran'],
                        ['XII TKJ 1', 'XII', 'TKJ', '2025/2026'],
                        ['XI RPL 2', 'XI', 'RPL', '2025/2026']],

            'ruang' => [['Kode Ruang', 'Nama Ruang', 'Kapasitas', 'Lokasi'],
                        ['R-01', 'Lab Komputer 1', 40, 'Gedung A Lt.2'],
                        ['R-02', 'Lab Komputer 2', 35, 'Gedung A Lt.3']],

            'guru' => [['NIP', 'Nama Lengkap', 'Username', 'Email', 'Mapel (kode/nama)', 'No HP', 'Alamat', 'Password'],
                       ['198501012010', 'Budi Santoso', 'budi.guru', 'budi@guru.com', 'MTK', '081234567890', 'Jl. Pendidikan No. 1', 'guru123'],
                       ['198601022011', 'Ani Rahmawati', 'ani.guru', 'ani@guru.com', 'IPA', '081234567891', 'Jl. Ilmu No. 5', 'guru123']],

            'proktor' => [['Nama', 'Username', 'Kode Ruang', 'Password'],
                          ['Proktor Lab 1', 'proktor1', 'R-01', 'proktor123'],
                          ['Proktor Lab 2', 'proktor2', 'R-02', 'proktor123']],

            'siswa' => [['NIS', 'NISN', 'Nama Lengkap', 'Jenis Kelamin (L/P)', 'Kelas', 'Jurusan (kode/nama)', 'No HP', 'Alamat'],
                        ['000001', '1234567890', 'Ahmad Fauzan', 'L', 'XII TKJ 1', 'TKJ', '081234567890', 'Jl. Merdeka No. 1'],
                        ['000002', '1234567891', 'Siti Nurhaliza', 'P', 'XII RPL 1', 'RPL', '081234567891', 'Jl. Sudirman No. 5']],

            'distribusi' => [['NIS / Nama Kelas', 'Kode Ruang'],
                             ['000001', 'R-01'],
                             ['000002', 'R-01'],
                             ['--- Mode Kelas ---', '---'],
                             ['XII TKJ 1', 'R-01'],
                             ['XII RPL 1', 'R-02']],

            default => [['Data']],
        };

        $filename = "template_import_{$type}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TemplateExport($headers),
            $filename
        );
    }
}
