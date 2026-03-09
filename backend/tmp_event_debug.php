<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$total = App\Models\Event::count();
$nullVendor = App\Models\Event::whereNull('vendor_id')->count();
$sample = App\Models\Event::select('id', 'title', 'vendor_id', 'is_active', 'starts_at')
    ->latest('id')
    ->limit(10)
    ->get()
    ->toArray();

echo json_encode([
    'total' => $total,
    'null_vendor' => $nullVendor,
    'sample' => $sample,
], JSON_PRETTY_PRINT), PHP_EOL;
