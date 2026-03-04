<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujian';

    protected $fillable = [
        'nama_ujian',
        'mapel_id',
        'guru_id',
        'durasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'token_ujian',
        'status',
        'acak_soal',
        'acak_opsi',
        'batasi_ip',
        'ip_allowed',
        'fullscreen_mode',
        'tampilkan_nilai',
        'jumlah_soal_tampil',
        'kkm',
        'keterangan',
    ];

    protected $appends = ['nama', 'token'];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'batasi_ip' => 'boolean',
        'fullscreen_mode' => 'boolean',
        'tampilkan_nilai' => 'boolean',
        'kkm' => 'decimal:2',
    ];

    /**
     * Accessor: $ujian->nama => $ujian->nama_ujian
     */
    public function getNamaAttribute(): ?string
    {
        return $this->nama_ujian;
    }

    /**
     * Accessor: $ujian->token => $ujian->token_ujian
     */
    public function getTokenAttribute(): ?string
    {
        return $this->token_ujian;
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function soal()
    {
        return $this->belongsToMany(Soal::class, 'ujian_soal')->withPivot('urutan');
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'ujian_kelas');
    }

    public function ruang()
    {
        return $this->belongsToMany(RuangUjian::class, 'ujian_ruang');
    }

    public function peserta()
    {
        return $this->hasMany(PesertaUjian::class);
    }

    public function hasilUjian()
    {
        return $this->hasMany(HasilUjian::class);
    }

    public function logAktivitas()
    {
        return $this->hasMany(LogAktivitas::class);
    }

    public function isAktif(): bool
    {
        return $this->status === 'publish' || $this->status === 'berlangsung';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    public static function generateToken(): string
    {
        do {
            $token = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (self::where('token_ujian', $token)->exists());

        return $token;
    }
}
