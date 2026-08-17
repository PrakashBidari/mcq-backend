<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE: intentionally not wrapped in DB::transaction() — MySQL implicitly
        // commits on DDL (ALTER TABLE), so mixing schema changes and data writes
        // inside one explicit transaction throws "There is no active transaction"
        // when Laravel later tries to commit/rollback. Data-loss risk is mitigated
        // instead by running this against a staging copy first (see plan verification).
        $usedCategoryIds = DB::table('books')
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id');

        $oldToNewId = [];
        $now = now();

        foreach ($usedCategoryIds as $oldId) {
            $category = DB::table('categories')->find($oldId);
            if (!$category) {
                continue;
            }

            $newId = DB::table('book_categories')->insertGetId([
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'color' => $category->color,
                'icon' => $category->icon,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $oldToNewId[$oldId] = $newId;
        }

        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        foreach ($oldToNewId as $oldId => $newId) {
            DB::table('books')->where('category_id', $oldId)->update(['category_id' => $newId]);
        }

        Schema::table('books', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('book_categories')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Not reversible: original categories.id mapping is not preserved once
        // book_categories rows are created. Restoring would require re-running
        // the create_books_table migration state; left intentionally as a no-op
        // safety net rather than silently corrupting data.
    }
};
