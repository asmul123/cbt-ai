<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'ujian_id',
        'aktivitas',
        'keterangan',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public static function log(int $userId, string $aktivitas, ?int $ujianId = null, ?string $keterangan = null): self
    {
        return self::create([
            'user_id' => $userId,
            'ujian_id' => $ujianId,
            'aktivitas' => $aktivitas,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
