<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SaveJawabanJob;

$start = (int)($argv[1] ?? 1);
$count = (int)($argv[2] ?? 1000);
$soalId = (int)($argv[3] ?? 1);

for ($i = 0; $i < $count; $i++) {
    $pesertaId = $start + $i;
    $jawaban = json_encode(['A']);
    SaveJawabanJob::dispatch($pesertaId, $soalId, $jawaban, false);
    if ($i % 50 === 0) echo "Dispatched: $i\n";
}

echo "Dispatched $count jobs for peserta starting $start soal $soalId\n";
