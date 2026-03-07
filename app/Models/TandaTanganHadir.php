<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TandaTanganHadir extends Model
{
    use HasFactory;

    protected $table = 'tanda_tangan_hadir';

    protected $fillable = [
        'berita_acara_id',
        'peserta_ujian_id',
        'tanda_tangan',
    ];

    public function beritaAcara()
    {
        return $this->belongsTo(BeritaAcara::class);
    }

    public function pesertaUjian()
    {
        return $this->belongsTo(PesertaUjian::class);
    }
}
