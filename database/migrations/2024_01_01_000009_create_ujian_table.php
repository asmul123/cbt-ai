<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ujian');
            $table->foreignId('mapel_id')->constrained('mapel')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->integer('durasi'); // menit
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->string('token_ujian', 10)->nullable()->unique();
            $table->enum('status', ['draft', 'publish', 'berlangsung', 'selesai'])->default('draft');
            $table->boolean('acak_soal')->default(false);
            $table->boolean('acak_opsi')->default(false);
            $table->boolean('batasi_ip')->default(false);
            $table->string('ip_allowed')->nullable(); // comma separated
            $table->boolean('fullscreen_mode')->default(true);
            $table->boolean('tampilkan_nilai')->default(false);
            $table->integer('jumlah_soal_tampil')->nullable(); // null = semua
            $table->decimal('kkm', 5, 2)->default(75);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian');
    }
};
