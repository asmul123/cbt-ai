<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('berita_acara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('ruang_ujian_id')->constrained('ruang_ujian')->cascadeOnDelete();
            $table->foreignId('proktor_id')->constrained('users')->cascadeOnDelete();
            $table->string('waktu_mulai', 10)->nullable(); // e.g. "07:00"
            $table->string('waktu_selesai', 10)->nullable(); // e.g. "08:30"
            $table->text('catatan')->nullable();
            $table->longText('ttd_pengawas')->nullable(); // base64 PNG signature
            $table->longText('ttd_pengawas_hadir')->nullable(); // base64 PNG signature for daftar hadir
            $table->json('peserta_tidak_hadir')->nullable(); // array of peserta_ujian_id
            $table->timestamps();

            $table->unique(['ujian_id', 'ruang_ujian_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acara');
    }
};
