<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mark the thai_bill migration as already run
$exists = DB::table('migrations')->where('migration', '2026_03_05_232513_add_thai_bill_columns_to_customershippings')->exists();
if (!$exists) {
    DB::table('migrations')->insert([
        'migration' => '2026_03_05_232513_add_thai_bill_columns_to_customershippings',
        'batch' => 99
    ]);
    echo "Marked thai_bill migration as done.\n";
} else {
    echo "thai_bill migration already marked.\n";
}
