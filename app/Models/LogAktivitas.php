<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\WriteLogAktivitasJob;

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

    public static function log(int $userId, string $aktivitas, ?int $ujianId = null, ?string $keterangan = null): void
    {
        // Tangkap ip & user-agent di sini (saat request masih ada),
        // lalu kirim ke queue agar DB write tidak memblokir response.
        WriteLogAktivitasJob::dispatch(
            $userId,
            $aktivitas,
            $ujianId,
            $keterangan,
            request()->ip(),
            request()->userAgent(),
        );
    }
}
