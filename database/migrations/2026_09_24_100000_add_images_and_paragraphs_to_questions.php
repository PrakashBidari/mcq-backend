<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reading passage shared by several questions. Each of its questions is still an
        // ordinary question (own options, own 1 mark) - the app just shows the paragraph
        // and all of its questions together on one page.
        Schema::create('paragraphs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::table('questions', function (Blueprint $table) {
            // Deleting a paragraph turns its questions back into normal questions.
            $table->foreignId('paragraph_id')->nullable()->after('position')
                ->constrained('paragraphs')->nullOnDelete();
            $table->string('image')->nullable()->after('question');
        });

        Schema::table('question_options', function (Blueprint $table) {
            // An option can be text, an image, or both - so text is no longer mandatory.
            $table->text('option_text')->nullable()->change();
            $table->string('option_image')->nullable()->after('option_text');
        });
    }

    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropColumn('option_image');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paragraph_id');
            $table->dropColumn('image');
        });

        Schema::dropIfExists('paragraphs');
    }
};
