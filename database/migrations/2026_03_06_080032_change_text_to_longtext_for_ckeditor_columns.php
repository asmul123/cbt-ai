<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah kolom text menjadi longText agar bisa menampung
     * gambar base64 dari CKEditor yang berukuran besar.
     */
    public function up(): void
    {
        Schema::table('soal', function (Blueprint $table) {
            $table->longText('soal')->change();
            $table->longText('pembahasan')->nullable()->change();
        });

        Schema::table('opsi', function (Blueprint $table) {
            $table->longText('teks')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soal', function (Blueprint $table) {
            $table->text('soal')->change();
            $table->text('pembahasan')->nullable()->change();
        });

        Schema::table('opsi', function (Blueprint $table) {
            $table->text('teks')->change();
        });
    }
};
