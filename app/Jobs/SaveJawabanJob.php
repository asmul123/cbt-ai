<?php

namespace App\Jobs;

use App\Models\JawabanSiswa;
use App\Models\PesertaUjian;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SaveJawabanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public readonly int $pesertaId,
        public readonly int $soalId,
        public readonly ?string $jawaban,
        public readonly bool $raguRagu = false,
    ) {}

    public function handle(): void
    {
        $peserta = PesertaUjian::findOrFail($this->pesertaId);

        JawabanSiswa::updateOrCreate(
            [
                'peserta_ujian_id' => $peserta->id,
                'soal_id' => $this->soalId,
            ],
            [
                'jawaban' => $this->jawaban,
                'ragu_ragu' => $this->raguRagu,
            ]
        );

        // Hapus cache setelah DB terupdate
        Cache::forget("jawaban:{$this->pesertaId}:{$this->soalId}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SaveJawabanJob failed", [
            'peserta_id' => $this->pesertaId,
            'soal_id' => $this->soalId,
            'error' => $exception->getMessage(),
        ]);
    }
}
