<?php
// Quick test script for registration endpoints
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== Registration System Test ===\n\n";

// Check calon siswa count
$calonSiswaTotal = User::where('role', 'calon_siswa')->count();
echo "Total calon siswa: $calonSiswaTotal\n";

// Check by status
$statuses = ['pending_documents', 'pending_approval', 'approved', 'rejected'];
foreach ($statuses as $status) {
    $count = User::where('role', 'calon_siswa')->where('registration_status', $status)->count();
    echo "Status '$status': $count\n";
}

echo "\n=== Sample pending approval users ===\n";
$pendingUsers = User::where('role', 'calon_siswa')
    ->where('registration_status', 'pending_approval')
    ->select('name', 'email', 'registration_status', 'submitted_at')
    ->limit(3)
    ->get();

foreach ($pendingUsers as $user) {
    echo "- {$user->name} ({$user->email}) - {$user->registration_status} - {$user->submitted_at}\n";
}

echo "\nTest completed!\n";