<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuangUjian extends Model
{
    use HasFactory;

    protected $table = 'ruang_ujian';

    protected $fillable = [
        'kode',
        'nama',
        'kapasitas',
        'lokasi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function proktor()
    {
        return $this->hasMany(User::class, 'ruang_ujian_id');
    }

    public function ujian()
    {
        return $this->belongsToMany(Ujian::class, 'ujian_ruang');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function pesertaUjian()
    {
        return $this->hasMany(PesertaUjian::class);
    }
}
