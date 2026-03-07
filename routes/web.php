<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Guru;
use App\Http\Controllers\Siswa;
use App\Http\Controllers\Proktor;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Redirect dashboard berdasarkan role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
    if ($user->hasRole('guru')) return redirect()->route('guru.dashboard');
    if ($user->hasRole('proktor')) return redirect()->route('proktor.dashboard');
    if ($user->hasRole('siswa')) return redirect()->route('siswa.dashboard');
    return redirect()->route('login');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== ADMIN ROUTES ====================
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Master Data
    Route::resource('jurusan', Admin\JurusanController::class)->except('show');
    Route::resource('kelas', Admin\KelasController::class)->except('show');
    Route::resource('mapel', Admin\MapelController::class)->except('show');
    Route::resource('guru', Admin\GuruController::class)->except('show');
    Route::resource('siswa', Admin\SiswaController::class)->except('show');
    Route::post('siswa/{siswa}/reset-password', [Admin\SiswaController::class, 'resetPassword'])->name('siswa.resetPassword');

    // Ruang Ujian & Proktor
    Route::resource('ruang', Admin\RuangUjianController::class);
    Route::post('ruang/{ruang}/add-siswa', [Admin\RuangUjianController::class, 'addSiswa'])->name('ruang.addSiswa');
    Route::post('ruang/{ruang}/remove-siswa/{siswa}', [Admin\RuangUjianController::class, 'removeSiswa'])->name('ruang.removeSiswa');
    Route::post('ruang/{ruang}/clear-siswa', [Admin\RuangUjianController::class, 'clearSiswa'])->name('ruang.clearSiswa');
    Route::post('ruang/{ruang}/add-ujian', [Admin\RuangUjianController::class, 'addUjian'])->name('ruang.addUjian');
    Route::post('ruang/{ruang}/remove-ujian/{ujian}', [Admin\RuangUjianController::class, 'removeUjian'])->name('ruang.removeUjian');
    Route::get('ruang-siswa-by-kelas', [Admin\RuangUjianController::class, 'siswaByKelas'])->name('ruang.siswaByKelas');
    Route::resource('proktor', Admin\ProktorController::class)->except('show');

    // Import Data
    Route::get('import', [Admin\ImportController::class, 'index'])->name('import.index');
    Route::post('import/kelas', [Admin\ImportController::class, 'importKelas'])->name('import.kelas');
    Route::post('import/ruang', [Admin\ImportController::class, 'importRuang'])->name('import.ruang');
    Route::post('import/guru', [Admin\ImportController::class, 'importGuru'])->name('import.guru');
    Route::post('import/proktor', [Admin\ImportController::class, 'importProktor'])->name('import.proktor');
    Route::post('import/siswa', [Admin\ImportController::class, 'importSiswa'])->name('import.siswa');
    Route::post('import/distribusi', [Admin\ImportController::class, 'importDistribusiRuang'])->name('import.distribusi');
    Route::get('import/template/{type}', [Admin\ImportController::class, 'downloadTemplate'])->name('import.template');
    Route::delete('import/reset/{type}', [Admin\ImportController::class, 'reset'])->name('import.reset');

    // Monitor Ujian (Admin)
    Route::get('monitor', [Admin\MonitorController::class, 'index'])->name('monitor.index');
    Route::get('monitor/{ujian}', [Admin\MonitorController::class, 'show'])->name('monitor.show');
    Route::get('monitor/{ujian}/data', [Admin\MonitorController::class, 'data'])->name('monitor.data');
    Route::post('monitor/{ujian}/buka/{peserta}', [Admin\MonitorController::class, 'bukaPeserta'])->name('monitor.buka');
    Route::post('monitor/{ujian}/hapus/{peserta}', [Admin\MonitorController::class, 'hapusPeserta'])->name('monitor.hapus');
    Route::post('monitor/{ujian}/reset/{peserta}', [Admin\MonitorController::class, 'resetPeserta'])->name('monitor.reset');
    Route::post('monitor/{ujian}/selesaikan/{peserta}', [Admin\MonitorController::class, 'selesaikanPeserta'])->name('monitor.selesaikan');

    // Ujian management (CRUD + soal + publish)
    Route::get('ujian', [Admin\UjianAdminController::class, 'index'])->name('ujian.index');
    Route::get('ujian/create', [Admin\UjianAdminController::class, 'create'])->name('ujian.create');
    Route::post('ujian', [Admin\UjianAdminController::class, 'store'])->name('ujian.store');
    Route::get('ujian/{ujian}/edit', [Admin\UjianAdminController::class, 'edit'])->name('ujian.edit');
    Route::put('ujian/{ujian}', [Admin\UjianAdminController::class, 'update'])->name('ujian.update');
    Route::get('ujian/{ujian}/soal', [Admin\UjianAdminController::class, 'soal'])->name('ujian.soal');
    Route::post('ujian/{ujian}/soal', [Admin\UjianAdminController::class, 'soalSync'])->name('ujian.soalSync');
    Route::post('ujian/{ujian}/publish', [Admin\UjianAdminController::class, 'publish'])->name('ujian.publish');
    Route::post('ujian/{ujian}/generate-token', [Admin\UjianAdminController::class, 'generateToken'])->name('ujian.generateToken');
    Route::patch('ujian/{ujian}/status', [Admin\UjianAdminController::class, 'updateStatus'])->name('ujian.updateStatus');
    Route::delete('ujian/{ujian}', [Admin\UjianAdminController::class, 'destroy'])->name('ujian.destroy');

    // Hasil & Penilaian (Admin)
    Route::get('hasil', [Admin\HasilController::class, 'index'])->name('hasil.index');
    Route::get('hasil/{ujian}', [Admin\HasilController::class, 'show'])->name('hasil.show');
    Route::get('hasil/{ujian}/siswa/{siswa}', [Admin\HasilController::class, 'detail'])->name('hasil.detail');
    Route::get('hasil/{ujian}/essay', [Admin\HasilController::class, 'nilaiEssay'])->name('hasil.essay');
    Route::post('hasil/essay/{jawaban}', [Admin\HasilController::class, 'simpanNilaiEssay'])->name('hasil.simpanEssay');

    // Quick Login Proktor
    Route::get('quick-login', [Admin\QuickLoginController::class, 'index'])->name('quick-login.index');
    Route::post('quick-login/login/{user}', [Admin\QuickLoginController::class, 'loginAs'])->name('quick-login.loginAs');

    // Cetak Berita Acara & Daftar Hadir
    Route::get('cetak', [Admin\CetakController::class, 'index'])->name('cetak.index');
    Route::get('cetak/berita-acara/{beritaAcara}', [Admin\CetakController::class, 'cetakBeritaAcara'])->name('cetak.beritaAcara');
    Route::get('cetak/daftar-hadir/{beritaAcara}', [Admin\CetakController::class, 'cetakDaftarHadir'])->name('cetak.daftarHadir');
});

// Kembali dari impersonate proktor ke admin (di luar group admin karena role aktif = proktor)
Route::post('admin/quick-login/kembali', [Admin\QuickLoginController::class, 'kembali'])
    ->middleware('auth')
    ->name('admin.quick-login.kembali');

// ==================== GURU ROUTES ====================
Route::prefix('guru')->middleware(['auth', 'role:guru'])->name('guru.')->group(function () {
    Route::get('/dashboard', [Guru\DashboardController::class, 'index'])->name('dashboard');

    // Bank Soal
    Route::post('soal/upload-gambar', [Guru\SoalController::class, 'uploadGambar'])->name('soal.uploadGambar');
    Route::post('soal/hapus-gambar', [Guru\SoalController::class, 'hapusGambar'])->name('soal.hapusGambar');
    Route::delete('soal/{soal}/hapus-gambar-soal', [Guru\SoalController::class, 'hapusGambarSoal'])->name('soal.hapusGambarSoal');
    Route::resource('soal', Guru\SoalController::class);
    Route::post('soal/{soal}/duplicate', [Guru\SoalController::class, 'duplicate'])->name('soal.duplicate');
    Route::get('soal-import', [Guru\SoalController::class, 'import'])->name('soal.import');
    Route::post('soal-import', [Guru\SoalController::class, 'importProcess'])->name('soal.importProcess');

    // Ujian
    Route::resource('ujian', Guru\UjianController::class);
    Route::get('ujian/{ujian}/soal', [Guru\UjianController::class, 'soal'])->name('ujian.soal');
    Route::post('ujian/{ujian}/soal', [Guru\UjianController::class, 'soalSync'])->name('ujian.soalSync');
    Route::post('ujian/{ujian}/publish', [Guru\UjianController::class, 'publish'])->name('ujian.publish');

    // Hasil & Nilai
    Route::get('hasil', [Guru\HasilController::class, 'index'])->name('hasil.index');
    Route::get('hasil/{ujian}', [Guru\HasilController::class, 'show'])->name('hasil.show');
    Route::get('hasil/{ujian}/siswa/{siswa}', [Guru\HasilController::class, 'detail'])->name('hasil.detail');
    Route::get('hasil/{ujian}/essay', [Guru\HasilController::class, 'nilaiEssay'])->name('hasil.essay');
    Route::post('hasil/essay/{jawaban}', [Guru\HasilController::class, 'simpanNilaiEssay'])->name('hasil.simpanEssay');

    // Analisis
    Route::get('analisis', [Guru\AnalisisController::class, 'index'])->name('analisis.index');
    Route::get('analisis/{ujian}', [Guru\AnalisisController::class, 'show'])->name('analisis.show');

    // Export
    Route::get('export/hasil/{ujian}/excel', [ExportController::class, 'excelHasil'])->name('export.excel');
    Route::get('export/hasil/{ujian}/pdf', [ExportController::class, 'pdfHasil'])->name('export.pdf');
    Route::get('export/berita-acara/{ujian}', [ExportController::class, 'pdfBeritaAcara'])->name('export.beritaAcara');
    Route::get('export/daftar-hadir/{ujian}', [ExportController::class, 'pdfDaftarHadir'])->name('export.daftarHadir');
});

// ==================== PROKTOR ROUTES ====================
Route::prefix('proktor')->middleware(['auth', 'role:proktor'])->name('proktor.')->group(function () {
    Route::get('/dashboard', [Proktor\MonitorController::class, 'dashboard'])->name('dashboard');
    Route::get('/monitor', [Proktor\MonitorController::class, 'index'])->name('monitor.index');
    Route::get('/monitor/{ujian}', [Proktor\MonitorController::class, 'show'])->name('monitor.show');
    Route::get('/monitor/{ujian}/data', [Proktor\MonitorController::class, 'data'])->name('monitor.data');
    Route::post('/monitor/{ujian}/buka/{peserta}', [Proktor\MonitorController::class, 'bukaPeserta'])->name('monitor.buka');
    Route::post('/monitor/{ujian}/hapus/{peserta}', [Proktor\MonitorController::class, 'hapusPeserta'])->name('monitor.hapus');
    Route::post('/monitor/{ujian}/reset/{peserta}', [Proktor\MonitorController::class, 'resetPeserta'])->name('monitor.reset');
    Route::post('/monitor/{ujian}/selesaikan/{peserta}', [Proktor\MonitorController::class, 'selesaikanPeserta'])->name('monitor.selesaikan');

    // Berita Acara
    Route::get('/berita-acara', [Proktor\BeritaAcaraController::class, 'index'])->name('berita-acara.index');
    Route::get('/berita-acara/{ujian}', [Proktor\BeritaAcaraController::class, 'create'])->name('berita-acara.create');
    Route::post('/berita-acara/{ujian}', [Proktor\BeritaAcaraController::class, 'store'])->name('berita-acara.store');

    // Daftar Hadir (proktor: lihat & TTD pengawas saja, siswa TTD sendiri)
    Route::get('/daftar-hadir/{ujian}', [Proktor\DaftarHadirController::class, 'show'])->name('daftar-hadir.show');
    Route::post('/daftar-hadir/ttd-pengawas', [Proktor\DaftarHadirController::class, 'simpanTtdPengawas'])->name('daftar-hadir.ttdPengawas');
});

// ==================== SISWA ROUTES ====================
Route::prefix('siswa')->middleware(['auth', 'role:siswa'])->name('siswa.')->group(function () {
    Route::get('/dashboard', [Siswa\SiswaController::class, 'dashboard'])->name('dashboard');
    Route::get('/ujian', [Siswa\SiswaController::class, 'ujianIndex'])->name('ujian.index');
    Route::get('/ujian/token', [Siswa\SiswaController::class, 'masukToken'])->name('ujian.token');
    Route::post('/ujian/token', [Siswa\SiswaController::class, 'verifikasiToken'])->name('ujian.verifikasiToken');
    Route::get('/ujian/{ujian}/konfirmasi', [Siswa\SiswaController::class, 'konfirmasi'])->name('ujian.konfirmasi');
    Route::post('/ujian/{ujian}/mulai', [Siswa\SiswaController::class, 'mulaiUjian'])->name('ujian.mulai');
    Route::get('/ujian/{ujian}/kerjakan/{nomor?}', [Siswa\SiswaController::class, 'kerjakan'])->name('ujian.kerjakan');
    Route::post('/ujian/{ujian}/jawab', [Siswa\SiswaController::class, 'simpanJawaban'])->name('ujian.jawab');
    Route::get('/ujian/{ujian}/submit', [Siswa\SiswaController::class, 'submitKonfirmasi'])->name('ujian.submitKonfirmasi');
    Route::post('/ujian/{ujian}/submit', [Siswa\SiswaController::class, 'submitUjian'])->name('ujian.submit');
    Route::get('/ujian/{ujian}/selesai', [Siswa\SiswaController::class, 'selesai'])->name('ujian.selesai');
    Route::get('/riwayat', [Siswa\SiswaController::class, 'riwayat'])->name('riwayat');
    Route::post('/log-aktivitas', [Siswa\SiswaController::class, 'logAktivitas'])->name('logAktivitas');

    // Daftar Hadir - TTD Siswa
    Route::get('/daftar-hadir', [Siswa\DaftarHadirController::class, 'index'])->name('daftar-hadir.index');
    Route::post('/daftar-hadir/ttd', [Siswa\DaftarHadirController::class, 'simpanTtd'])->name('daftar-hadir.simpanTtd');
});

require __DIR__.'/auth.php';
