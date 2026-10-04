<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_sets', function (Blueprint $table) {
            // Score (in %) needed to pass this set (null = app default of 60%)
            $table->unsignedTinyInteger('pass_percentage')->nullable()->after('time_limit');
        });
    }

    public function down(): void
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->dropColumn('pass_percentage');
        });
    }
};
