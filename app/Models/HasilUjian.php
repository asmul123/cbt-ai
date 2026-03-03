<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
    use HasFactory;

    protected $table = 'hasil_ujian';

    protected $fillable = [
        'peserta_ujian_id',
        'ujian_id',
        'siswa_id',
        'jumlah_soal',
        'benar_pg',
        'skor_pg',
        'skor_essay',
        'skor_isian',
        'nilai_akhir',
        'status_kelulusan',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_pengerjaan',
    ];

    protected $casts = [
        'skor_pg' => 'decimal:2',
        'skor_essay' => 'decimal:2',
        'skor_isian' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function pesertaUjian()
    {
        return $this->belongsTo(PesertaUjian::class);
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function isLulus(): bool
    {
        return $this->status_kelulusan === 'lulus';
    }
}
