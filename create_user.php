<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$siswa = App\Models\Siswa::find(8);
$user = App\Models\User::find(62);
if ($user && $siswa) {
    $siswa->update(['user_id' => $user->id]);
    $user->assignRole('siswa');
    echo "Updated user_id and assigned role\n";
} else {
    echo "User or Siswa not found\n";
}