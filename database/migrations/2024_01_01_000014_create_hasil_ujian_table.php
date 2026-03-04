<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_ujian_id')->constrained('peserta_ujian')->cascadeOnDelete();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->integer('jumlah_soal')->default(0);
            $table->integer('benar_pg')->default(0);
            $table->decimal('skor_pg', 5, 2)->default(0);
            $table->decimal('skor_essay', 5, 2)->default(0);
            $table->decimal('skor_isian', 5, 2)->default(0);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->enum('status_kelulusan', ['lulus', 'tidak_lulus', 'belum_dinilai'])->default('belum_dinilai');
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->integer('durasi_pengerjaan')->nullable(); // detik
            $table->timestamps();

            $table->unique(['ujian_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_ujian');
    }
};
