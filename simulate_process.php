<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SaveJawabanJob;
use Illuminate\Support\Facades\Cache;

$pesertaId = (int)($argv[1] ?? 1);
$soalId = (int)($argv[2] ?? 1);
$count = (int)($argv[3] ?? 10);

for ($i = 0; $i < $count; $i++) {
    $jawaban = json_encode(['A']);
    // write cache first as controller would
    Cache::put("jawaban_cache:{$pesertaId}:{$soalId}", ['soal_id'=>$soalId,'jawaban'=>$jawaban,'ragu_ragu'=>false], now()->addHours(3));
    // directly run job handler synchronously
    $job = new SaveJawabanJob($pesertaId, $soalId, $jawaban, false);
    try {
        $job->handle();
        echo "Processed job #".($i+1)."\n";
    } catch (Throwable $e) {
        echo "Job failed: " . $e->getMessage() . "\n";
    }
}

echo "Done processing $count jobs\n";
