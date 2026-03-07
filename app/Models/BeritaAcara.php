<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    use HasFactory;

    protected $table = 'berita_acara';

    protected $fillable = [
        'ujian_id',
        'ruang_ujian_id',
        'proktor_id',
        'nama_pengawas',
        'waktu_mulai',
        'waktu_selesai',
        'catatan',
        'ttd_pengawas',
        'ttd_pengawas_hadir',
        'peserta_tidak_hadir',
    ];

    protected $casts = [
        'peserta_tidak_hadir' => 'array',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function ruangUjian()
    {
        return $this->belongsTo(RuangUjian::class);
    }

    public function proktor()
    {
        return $this->belongsTo(User::class, 'proktor_id');
    }

    public function tandaTanganHadir()
    {
        return $this->hasMany(TandaTanganHadir::class);
    }

    /**
     * Cek apakah peserta tertentu tidak hadir
     */
    public function isTidakHadir(int $pesertaUjianId): bool
    {
        return in_array($pesertaUjianId, $this->peserta_tidak_hadir ?? []);
    }

    /**
     * Jumlah peserta hadir
     */
    public function jumlahHadir(): int
    {
        $totalPeserta = PesertaUjian::where('ujian_id', $this->ujian_id)
            ->where('ruang_ujian_id', $this->ruang_ujian_id)
            ->count();

        return $totalPeserta - count($this->peserta_tidak_hadir ?? []);
    }

    /**
     * Jumlah peserta tidak hadir
     */
    public function jumlahTidakHadir(): int
    {
        return count($this->peserta_tidak_hadir ?? []);
    }
}
