<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Allows the admin "manual grant" purchase recovery tool to record a platform value
// distinct from the two real store platforms, so those rows are clearly distinguishable
// from an actual ios/android purchase in the admin purchases list.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE purchases MODIFY platform ENUM('ios', 'android', 'manual') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE purchases MODIFY platform ENUM('ios', 'android') NOT NULL");
    }
};
