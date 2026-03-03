<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('ruang_ujian_id')->nullable()->after('alamat')
                ->constrained('ruang_ujian')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropForeign(['ruang_ujian_id']);
            $table->dropColumn('ruang_ujian_id');
        });
    }
};
