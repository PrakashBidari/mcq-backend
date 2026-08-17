<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('blog_category_id')->nullable()->after('category')
                ->constrained('blog_categories')->restrictOnDelete();
        });

        $distinctNames = DB::table('blogs')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        $now = now();
        $nameToId = [];

        foreach ($distinctNames as $name) {
            $slug = Str::slug($name);
            $existing = DB::table('blog_categories')->where('slug', $slug)->first();

            if ($existing) {
                $nameToId[$name] = $existing->id;
                continue;
            }

            $nameToId[$name] = DB::table('blog_categories')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($nameToId as $name => $id) {
            DB::table('blogs')->where('category', $name)->update(['blog_category_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropForeign(['blog_category_id']);
            $table->dropColumn('blog_category_id');
        });
    }
};
