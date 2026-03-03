<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nama',
        'jenis_kelamin',
        'kelas_id',
        'jurusan_id',
        'no_hp',
        'alamat',
        'ruang_ujian_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function ruangUjian()
    {
        return $this->belongsTo(RuangUjian::class);
    }

    public function pesertaUjian()
    {
        return $this->hasMany(PesertaUjian::class);
    }

    public function hasilUjian()
    {
        return $this->hasMany(HasilUjian::class);
    }
}
