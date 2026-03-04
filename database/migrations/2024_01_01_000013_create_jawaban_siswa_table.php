<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_ujian_id')->constrained('peserta_ujian')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->text('jawaban')->nullable(); // ID opsi / text isian / text essay
            $table->boolean('ragu_ragu')->default(false);
            $table->boolean('is_benar')->nullable(); // null = belum dinilai (essay)
            $table->decimal('skor', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['peserta_ujian_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswa');
    }
};
