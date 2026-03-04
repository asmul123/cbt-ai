<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaUjian extends Model
{
    use HasFactory;

    protected $table = 'peserta_ujian';

    protected $fillable = [
        'ujian_id',
        'siswa_id',
        'status',
        'waktu_mulai',
        'waktu_selesai',
        'ip_address',
        'soal_order',
        'jumlah_pelanggaran',
        'ruang_ujian_id',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'soal_order' => 'array',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jawabanSiswa()
    {
        return $this->hasMany(JawabanSiswa::class);
    }

    public function ruangUjian()
    {
        return $this->belongsTo(RuangUjian::class);
    }

    public function hasilUjian()
    {
        return $this->hasOne(HasilUjian::class);
    }

    public function isBelumMulai(): bool
    {
        return $this->status === 'belum_mulai';
    }

    public function isMengerjakan(): bool
    {
        return $this->status === 'mengerjakan';
    }

    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    public function sisaWaktu(): int
    {
        if (!$this->waktu_mulai) return $this->ujian->durasi * 60;

        $elapsed = now()->diffInSeconds($this->waktu_mulai);
        $total = $this->ujian->durasi * 60;

        return max(0, $total - $elapsed);
    }
}
