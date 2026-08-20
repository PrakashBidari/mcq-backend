<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // 'about-app' | 'privacy-policy'
            $table->string('tagline')->nullable(); // about only
            $table->string('last_updated_label')->nullable(); // privacy only
            $table->text('intro_text_1')->nullable(); // about: aboutText1 / privacy: introText
            $table->text('intro_text_2')->nullable(); // about only: aboutText2
            $table->string('stat_1_value')->nullable(); // about only
            $table->string('stat_2_value')->nullable();
            $table->string('stat_3_value')->nullable();
            $table->string('stat_4_value')->nullable();
            $table->string('developer_name')->nullable(); // about only
            $table->string('developer_role')->nullable();
            $table->string('developer_url')->nullable();
            $table->string('copyright_text')->nullable(); // about only
            $table->json('items')->nullable(); // about: features [{title,content}] / privacy: sections [{title,content}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_pages');
    }
};
