<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per device per day (see TrackVisit middleware) - feeds the dashboard's
        // "visitors" and "active users" charts.
        Schema::create('daily_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date');
            // sha1 of ip + user agent - identifies a device without storing either
            $table->string('visitor_key', 40);
            // Set once the device makes a signed-in request that day
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['visit_date', 'visitor_key']);
            $table->index(['visit_date', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_visits');
    }
};
