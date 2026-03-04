<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapel';

    protected $fillable = [
        'kode',
        'nama',
        'jurusan_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function guru()
    {
        return $this->hasMany(Guru::class);
    }

    public function soal()
    {
        return $this->hasMany(Soal::class);
    }

    public function ujian()
    {
        return $this->hasMany(Ujian::class);
    }
}
