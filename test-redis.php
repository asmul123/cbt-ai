<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $redis = cache()->store('redis');
    $redis->put('test_key', 'test_value', 10);
    $value = $redis->get('test_key');
    echo "✓ Redis Connection: SUCCESS\n";
    echo "✓ Test Value: " . $value . "\n";
} catch (Exception $e) {
    echo "✗ Redis Connection: FAILED\n";
    echo "✗ Error: " . $e->getMessage() . "\n";
}
