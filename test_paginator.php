<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$paginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
var_dump($paginator->isEmpty());
