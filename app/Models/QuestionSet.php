<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QuestionSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'package_id',
        'name',
        'description',
        'is_active',
        'is_paid',
        'price',
        'price_tier',
        'price_tier_id',
        'trial_enabled',
        'trial_type',
        'trial_value',
        'access_type',
        'access_value',
        'time_limit',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_paid'       => 'boolean',
        'price'         => 'decimal:2',
        'trial_enabled' => 'boolean',
        'trial_value'   => 'integer',
        'access_value'  => 'integer',
        'time_limit'    => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function package()
    {
        return $this->belongsTo(QuestionSetPackage::class, 'package_id');
    }

    public function priceTier()
    {
        return $this->belongsTo(PriceTier::class);
    }

    public function isPackaged(): bool
    {
        return !is_null($this->package_id);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_question_set')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function isOwnedBy(?int $userId): bool
    {
        if (!$this->is_paid) {
            return true;
        }

        if (!$userId) {
            return false;
        }

        return Purchase::userOwns($userId, $this->id);
    }

    // Sets each set's `paragraphs_count` (distinct reading paragraphs among its
    // questions) with a single query for the whole list.
    public static function loadParagraphsCount(iterable $sets): void
    {
        $sets = collect($sets);
        if ($sets->isEmpty()) {
            return;
        }

        $counts = DB::table('question_question_set')
            ->join('questions', 'questions.id', '=', 'question_question_set.question_id')
            ->whereIn('question_question_set.question_set_id', $sets->pluck('id'))
            ->whereNotNull('questions.paragraph_id')
            ->groupBy('question_question_set.question_set_id')
            ->selectRaw('question_question_set.question_set_id as set_id, COUNT(DISTINCT questions.paragraph_id) as total')
            ->pluck('total', 'set_id');

        foreach ($sets as $set) {
            $set->paragraphs_count = (int) ($counts[$set->id] ?? 0);
        }
    }
}
