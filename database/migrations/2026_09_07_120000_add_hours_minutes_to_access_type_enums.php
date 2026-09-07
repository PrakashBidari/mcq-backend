<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A paid item / package can now sell access in attempts, days, hours OR minutes
     * (still exactly one at a time). Widen the enum on the three tables that store an
     * access_type so the shorter windows are accepted and persisted.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `question_sets` MODIFY `access_type` ENUM('attempts','days','hours','minutes') NULL");
        DB::statement("ALTER TABLE `question_set_packages` MODIFY `access_type` ENUM('attempts','days','hours','minutes') NULL");
        DB::statement("ALTER TABLE `purchases` MODIFY `access_type` ENUM('attempts','days','hours','minutes') NULL");
    }

    public function down(): void
    {
        // Collapse any of the new units back to the closest legacy value so the
        // narrower enum can be re-applied without truncation errors.
        DB::statement("UPDATE `question_sets` SET `access_type` = 'days' WHERE `access_type` IN ('hours','minutes')");
        DB::statement("UPDATE `question_set_packages` SET `access_type` = 'days' WHERE `access_type` IN ('hours','minutes')");
        DB::statement("UPDATE `purchases` SET `access_type` = 'days' WHERE `access_type` IN ('hours','minutes')");

        DB::statement("ALTER TABLE `question_sets` MODIFY `access_type` ENUM('attempts','days') NULL");
        DB::statement("ALTER TABLE `question_set_packages` MODIFY `access_type` ENUM('attempts','days') NULL");
        DB::statement("ALTER TABLE `purchases` MODIFY `access_type` ENUM('attempts','days') NULL");
    }
};
