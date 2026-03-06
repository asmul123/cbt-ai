<?php

namespace App\Jobs;

use App\Models\LogAktivitas;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WriteLogAktivitasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah retry jika job gagal (misal DB down sesaat).
     */
    public int $tries = 3;

    public function __construct(
        public readonly int     $userId,
        public readonly string  $aktivitas,
        public readonly ?int    $ujianId,
        public readonly ?string $keterangan,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
    ) {
        // Jangan dispatch ke queue sampai transaksi DB commit.
        // Penting untuk call dari dalam DB::transaction() seperti di UjianService.
        $this->afterCommit();
    }

    public function handle(): void
    {
        LogAktivitas::create([
            'user_id'    => $this->userId,
            'ujian_id'   => $this->ujianId,
            'aktivitas'  => $this->aktivitas,
            'keterangan' => $this->keterangan,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ]);
    }
}
