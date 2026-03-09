<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$checks = [
  'users' => fn() => App\Models\User::query()->count(),
  'events' => fn() => App\Models\Event::query()->count(),
  'vendors' => fn() => App\Models\User::query()->whereIn('role', ['vendor', 'admin'])->count(),
];

foreach ($checks as $name => $fn) {
  $start = microtime(true);
  try {
    $value = $fn();
    $ms = (int) ((microtime(true) - $start) * 1000);
    echo $name . '=' . $value . ' ms=' . $ms . PHP_EOL;
  } catch (Throwable $e) {
    $ms = (int) ((microtime(true) - $start) * 1000);
    echo $name . '=ERROR ms=' . $ms . ' msg=' . $e->getMessage() . PHP_EOL;
  }
}
