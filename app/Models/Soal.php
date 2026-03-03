<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'soal';

    protected $fillable = [
        'mapel_id',
        'guru_id',
        'tipe_soal',
        'soal',
        'gambar',
        'tingkat_kesulitan',
        'kompetensi_dasar',
        'pembahasan',
        'bobot',
        'status',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
    ];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function opsi()
    {
        return $this->hasMany(Opsi::class)->orderBy('urutan');
    }

    public function ujian()
    {
        return $this->belongsToMany(Ujian::class, 'ujian_soal')->withPivot('urutan');
    }

    public function jawabanSiswa()
    {
        return $this->hasMany(JawabanSiswa::class);
    }

    public function opsiBenar()
    {
        return $this->hasMany(Opsi::class)->where('is_benar', true);
    }

    public function isPG(): bool
    {
        return $this->tipe_soal === 'pg';
    }

    public function isPGKompleks(): bool
    {
        return $this->tipe_soal === 'pg_kompleks';
    }

    public function isIsian(): bool
    {
        return $this->tipe_soal === 'isian';
    }

    public function isEssay(): bool
    {
        return $this->tipe_soal === 'essay';
    }
}
