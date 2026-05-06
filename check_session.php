<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'Session driver: '.config('session.driver')."\n";
echo 'Session domain: '.config('session.domain')."\n";
echo 'Session secure: '.config('session.secure')."\n";
echo 'App URL: '.config('app.url')."\n";
echo 'Session path: '.config('session.path')."\n";
echo 'Session files dir: '.storage_path('framework/sessions')."\n";
echo 'Dir writable: '.(is_writable(storage_path('framework/sessions')) ? 'YES' : 'NO')."\n";
