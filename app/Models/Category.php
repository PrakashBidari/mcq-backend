<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
    ];

    // Every category keyed by id, loaded once per request. The tree can be any
    // depth, so the helpers below walk this in memory instead of querying per level.
    protected static ?Collection $allKeyed = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::$allKeyed = null);
        static::deleted(fn () => static::$allKeyed = null);
    }

    public function questionSets()
    {
        return $this->hasMany(QuestionSet::class);
    }

    public function packages()
    {
        return $this->hasMany(QuestionSetPackage::class, 'subcategory_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isSubcategory(): bool
    {
        return !is_null($this->parent_id);
    }

    public static function allKeyed(): Collection
    {
        return static::$allKeyed ??= static::orderBy('name')->get()->keyBy('id');
    }

    // Ancestors from the top-level category down to the direct parent (excludes self)
    public function ancestors(): Collection
    {
        $all = static::allKeyed();
        $ancestors = [];
        $seen = [$this->id => true];
        $parentId = $this->parent_id;

        while ($parentId && !isset($seen[$parentId]) && ($parent = $all->get($parentId))) {
            $seen[$parentId] = true;
            array_unshift($ancestors, $parent);
            $parentId = $parent->parent_id;
        }

        return collect($ancestors);
    }

    // The top-level category this one sits under (itself when it is top-level)
    public function root(): self
    {
        return $this->ancestors()->first() ?? $this;
    }

    // e.g. "JLPT → N5 → Grammar"; pass $withRoot = false for "N5 → Grammar"
    public function pathName(string $separator = ' → ', bool $withRoot = true): string
    {
        $names = $this->ancestors()->pluck('name')->push($this->name);

        if (!$withRoot) {
            $names->shift();
        }

        return $names->implode($separator);
    }

    // Ids of every category below this one, at any depth (excludes self)
    public function descendantIds(): array
    {
        $childrenByParent = static::allKeyed()->groupBy('parent_id');
        $ids = [];
        $queue = [$this->id];

        while ($queue) {
            foreach ($childrenByParent->get(array_shift($queue), []) as $child) {
                if (!in_array($child->id, $ids, true)) {
                    $ids[] = $child->id;
                    $queue[] = $child->id;
                }
            }
        }

        return $ids;
    }

    public function selfAndDescendantIds(): array
    {
        return array_merge([$this->id], $this->descendantIds());
    }

    // Ids from the top-level category down to (and including) the given category
    public static function pathIds($id): array
    {
        $category = $id ? static::allKeyed()->get((int) $id) : null;

        return $category
            ? $category->ancestors()->pluck('id')->push($category->id)->all()
            : [];
    }

    // The whole tree as nested [id, name, children] arrays, for the admin cascading selects
    public static function treeArray(): array
    {
        $childrenByParent = static::allKeyed()->groupBy(fn ($category) => $category->parent_id ?? 0);

        $build = function ($parentId) use (&$build, $childrenByParent) {
            return $childrenByParent->get($parentId, collect())->map(fn ($category) => [
                'id'       => $category->id,
                'name'     => $category->name,
                'children' => $build($category->id),
            ])->values()->all();
        };

        return $build(0);
    }

    // The whole tree flattened depth-first (parents before their children), each item
    // carrying its depth, top-level id and full path - for indented dropdowns.
    // $excludeIds drops those categories together with everything below them.
    public static function flatTree(array $excludeIds = []): Collection
    {
        $childrenByParent = static::allKeyed()->groupBy(fn ($category) => $category->parent_id ?? 0);
        $flat = collect();

        $walk = function ($parentId, int $depth, ?int $rootId, array $names) use (&$walk, $childrenByParent, $excludeIds, $flat) {
            foreach ($childrenByParent->get($parentId, []) as $category) {
                if (in_array($category->id, $excludeIds)) {
                    continue;
                }

                $path = array_merge($names, [$category->name]);

                $flat->push((object) [
                    'id'       => $category->id,
                    'name'     => $category->name,
                    'depth'    => $depth,
                    'root_id'  => $rootId ?? $category->id,
                    'path'     => implode(' → ', $path),
                    'sub_path' => implode(' → ', array_slice($path, 1)),
                ]);

                $walk($category->id, $depth + 1, $rootId ?? $category->id, $path);
            }
        };

        $walk(0, 0, null, []);

        return $flat;
    }

    // Get all questions through question sets
    public function questions()
    {
        return $this->hasManyThrough(
            Question::class,
            QuestionSet::class,
            'category_id',      // Foreign key on question_sets
            'id',               // Foreign key on questions
            'id',               // Local key on categories
            'id'                // Local key on question_sets
        )->join('question_question_set', 'questions.id', '=', 'question_question_set.question_id')
         ->where('question_question_set.question_set_id', '=', \DB::raw('question_sets.id'));
    }
}
