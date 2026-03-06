<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah index untuk kolom yang sering di-WHERE / JOIN.
 *
 * Catatan:
 * - Unique constraint sudah otomatis membuat index, tapi hanya pada kolom pertama.
 *   Contoh: unique(ujian_id, siswa_id) → mencakup query WHERE ujian_id=?, tapi
 *   TIDAK mencakup query WHERE siswa_id=? saja.
 * - PostgreSQL TIDAK otomatis membuat index untuk foreign key (berbeda dengan MySQL).
 * - Semua index baru di bawah ini bersifat non-unique (index biasa).
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- ujian ---
        Schema::table('ujian', function (Blueprint $table) {
            $table->index('guru_id',   'idx_ujian_guru_id');   // filter by guru (halaman guru)
            $table->index('status',    'idx_ujian_status');    // WHERE status IN (...)
            $table->index('mapel_id',  'idx_ujian_mapel_id'); // filter by mapel
        });

        // --- soal ---
        Schema::table('soal', function (Blueprint $table) {
            $table->index('mapel_id',          'idx_soal_mapel_id');  // bank soal by mapel
            $table->index('guru_id',           'idx_soal_guru_id');   // bank soal by guru
            $table->index('status',            'idx_soal_status');    // filter soal aktif
        });

        // --- peserta_ujian ---
        // Unique (ujian_id, siswa_id) sudah memberi index untuk ujian_id,
        // tapi TIDAK untuk siswa_id. Tambah index siswa_id.
        Schema::table('peserta_ujian', function (Blueprint $table) {
            $table->index('siswa_id', 'idx_peserta_ujian_siswa_id'); // cek ujian aktif siswa
        });

        // --- jawaban_siswa ---
        // Unique (peserta_ujian_id, soal_id) sudah memberi index untuk peserta_ujian_id,
        // tapi TIDAK untuk soal_id. Tambah index soal_id.
        Schema::table('jawaban_siswa', function (Blueprint $table) {
            $table->index('soal_id', 'idx_jawaban_siswa_soal_id'); // analisis per soal
        });

        // --- hasil_ujian ---
        // Unique (ujian_id, siswa_id) sudah memberi index untuk ujian_id,
        // tapi TIDAK untuk siswa_id dan peserta_ujian_id.
        Schema::table('hasil_ujian', function (Blueprint $table) {
            $table->index('siswa_id',         'idx_hasil_ujian_siswa_id');         // riwayat siswa
            $table->index('peserta_ujian_id', 'idx_hasil_ujian_peserta_ujian_id'); // join di hitungNilai
        });

        // --- log_aktivitas ---
        // Foreign key tidak otomatis membuat index di PostgreSQL.
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->index('ujian_id', 'idx_log_aktivitas_ujian_id'); // monitor log per ujian
            $table->index('user_id',  'idx_log_aktivitas_user_id');  // log per user
        });
    }

    public function down(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropIndex('idx_log_aktivitas_ujian_id');
            $table->dropIndex('idx_log_aktivitas_user_id');
        });

        Schema::table('hasil_ujian', function (Blueprint $table) {
            $table->dropIndex('idx_hasil_ujian_siswa_id');
            $table->dropIndex('idx_hasil_ujian_peserta_ujian_id');
        });

        Schema::table('jawaban_siswa', function (Blueprint $table) {
            $table->dropIndex('idx_jawaban_siswa_soal_id');
        });

        Schema::table('peserta_ujian', function (Blueprint $table) {
            $table->dropIndex('idx_peserta_ujian_siswa_id');
        });

        Schema::table('soal', function (Blueprint $table) {
            $table->dropIndex('idx_soal_mapel_id');
            $table->dropIndex('idx_soal_guru_id');
            $table->dropIndex('idx_soal_status');
        });

        Schema::table('ujian', function (Blueprint $table) {
            $table->dropIndex('idx_ujian_guru_id');
            $table->dropIndex('idx_ujian_status');
            $table->dropIndex('idx_ujian_mapel_id');
        });
    }
};
