<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->default('Get in Touch');
            $table->text('hero_subtitle')->default('We\'d love to hear from you! Send us a message and we\'ll respond as soon as possible.');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->string('form_title')->default('Send us a Message');
            $table->text('form_subtitle')->default('Fill out the form below and we\'ll get back to you shortly.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};
