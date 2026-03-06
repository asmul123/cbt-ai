<?php
// resend_pending.php
// Safe resender script: scan Redis for jawaban_cache keys and re-dispatch SaveJawabanJob
// Run as the application user (serverujian) with proper env (DB_DATABASE etc.)

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SaveJawabanJob;

$redis = app('redis')->connection();

$maxPerRun = 500; // limit number of re-dispatch per run
$dispatched = 0;
$cursor = null;

// Use SCAN to iterate keys safely
$iterator = null;
try {
    while ($dispatched < $maxPerRun) {
        $result = $redis->scan($iterator, ['match' => 'jawaban_cache:*', 'count' => 1000]);
        if ($result === false) break;
        foreach ($result as $key) {
            if ($dispatched >= $maxPerRun) break 2;
            // parse key format jawaban_cache:{peserta}:{soal}
            $parts = explode(':', $key);
            if (count($parts) < 3) continue;
            $pesertaId = (int)$parts[1];
            $soalId = (int)$parts[2];

            // skip if already persisted
            $persistKey = "jawaban_persisted:{$pesertaId}:{$soalId}";
            if ($redis->get($persistKey)) {
                // clean up stale cache if DB already has it
                $redis->del($key);
                continue;
            }

            // quick DB check to avoid unnecessary dispatch
            $exists = DB::table('jawaban_siswa')
                ->where('peserta_ujian_id', $pesertaId)
                ->where('soal_id', $soalId)
                ->exists();
            if ($exists) {
                $redis->setex($persistKey, 6*3600, now()->toDateTimeString());
                $redis->del($key);
                continue;
            }

            // fetch payload from cache (may be array or json)
            $payload = $redis->get($key);
            if (!$payload) continue;
            $data = json_decode($payload, true);
            if (!is_array($data)) {
                // if payload not json, try to unserialize via Cache::get
                $data = Cache::get($key);
                if (!is_array($data)) {
                    // fallback: skip
                    Log::warning("resender: unable to decode payload for $key");
                    continue;
                }
            }

            $jawaban = $data['jawaban'] ?? null;
            $ragu = $data['ragu_ragu'] ?? false;

            // dispatch job safely
            SaveJawabanJob::dispatch($pesertaId, $soalId, $jawaban, (bool)$ragu)->onQueue('default');
            $dispatched++;
        }
        if ($iterator === 0 || $iterator === null) break; // finished
    }
} catch (Exception $e) {
    Log::error('resender error: '.$e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Resender finished. Dispatched: $dispatched jobs\n";
return 0;
