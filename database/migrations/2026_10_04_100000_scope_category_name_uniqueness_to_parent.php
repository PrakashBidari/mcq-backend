<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Categories can now nest to any depth, so the same name has to be allowed under
    // different parents (e.g. "Grammar" under both N5 and N4). Uniqueness among
    // siblings is enforced by CategoryController's validation instead.
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }
};
