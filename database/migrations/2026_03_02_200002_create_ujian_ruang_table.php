<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian_ruang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('ruang_ujian_id')->constrained('ruang_ujian')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ujian_id', 'ruang_ujian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_ruang');
    }
};
