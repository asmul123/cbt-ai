<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanSiswa extends Model
{
    use HasFactory;

    protected $table = 'jawaban_siswa';

    protected $fillable = [
        'peserta_ujian_id',
        'soal_id',
        'jawaban',
        'ragu_ragu',
        'is_benar',
        'skor',
    ];

    protected $casts = [
        'ragu_ragu' => 'boolean',
        'is_benar' => 'boolean',
        'skor' => 'decimal:2',
    ];

    public function pesertaUjian()
    {
        return $this->belongsTo(PesertaUjian::class);
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }
}
