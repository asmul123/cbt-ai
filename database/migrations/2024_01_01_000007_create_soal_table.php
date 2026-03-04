<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapel_id')->constrained('mapel')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->enum('tipe_soal', ['pg', 'pg_kompleks', 'isian', 'essay']);
            $table->text('soal'); // HTML content from WYSIWYG
            $table->string('gambar')->nullable();
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit']);
            $table->string('kompetensi_dasar')->nullable();
            $table->decimal('bobot', 5, 2)->default(1);
            $table->enum('status', ['draft', 'aktif', 'nonaktif'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
