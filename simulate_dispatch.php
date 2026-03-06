<?php
// Simulate dispatching SaveJawabanJob multiple times
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SaveJawabanJob;

$pesertaId = (int)($argv[1] ?? 1);
$soalId = (int)($argv[2] ?? 1);
$count = (int)($argv[3] ?? 10);

for ($i = 0; $i < $count; $i++) {
    $jawaban = json_encode(['A']);
    SaveJawabanJob::dispatch($pesertaId, $soalId, $jawaban, false);
    if ($i % 50 === 0) echo "Dispatched: $i\n";
}

echo "Dispatched $count jobs for peserta $pesertaId soal $soalId\n";
