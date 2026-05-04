<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$elevator = App\Models\Elevator::first();
echo "Code: " . $elevator->code . "\n";
echo "Name: " . $elevator->name . "\n";
echo "Name ?: Code ?: ID -> " . ($elevator->name ?: ($elevator->code ?: ('ID: ' . $elevator->id))) . "\n";
