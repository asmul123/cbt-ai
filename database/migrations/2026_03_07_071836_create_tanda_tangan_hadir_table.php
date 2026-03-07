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
        Schema::create('tanda_tangan_hadir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_acara_id')->constrained('berita_acara')->cascadeOnDelete();
            $table->foreignId('peserta_ujian_id')->constrained('peserta_ujian')->cascadeOnDelete();
            $table->longText('tanda_tangan'); // base64 PNG
            $table->timestamps();

            $table->unique(['berita_acara_id', 'peserta_ujian_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanda_tangan_hadir');
    }
};
