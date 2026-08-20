<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserQuizAttempt;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    // How much of a question set's allotted time counts as "fast" for the
    // Quick Learner badge - finishing in well under the allowed time.
    private const QUICK_LEARNER_TIME_RATIO = 0.6;
    private const QUICK_LEARNER_THRESHOLD = 3;
    private const QUIZ_MASTER_THRESHOLD = 10;
    private const PERFECTIONIST_THRESHOLD = 10;
    private const STREAK_THRESHOLD = 30;

    public function index(Request $request)
    {
        $attempts = UserQuizAttempt::with('questionSet.category')
            ->where('user_id', $request->user()->id)
            ->orderBy('completed_at', 'desc')
            ->get();

        $perfectScores = $attempts->where('percentage', 100)->count();
        $highScores = $attempts->filter(fn ($a) => (float) $a->percentage >= 90)->count();

        $distinctCategories = $attempts
            ->filter(fn ($a) => $a->questionSet && $a->questionSet->category_id)
            ->pluck('questionSet.category_id')
            ->unique()
            ->count();

        $quickLearnerCount = $attempts->filter(function ($a) {
            if (!$a->time_taken_seconds || !$a->questionSet || !$a->questionSet->time_limit) {
                return false;
            }
            $allottedSeconds = $a->questionSet->time_limit * 60;
            return $a->time_taken_seconds <= self::QUICK_LEARNER_TIME_RATIO * $allottedSeconds;
        })->count();

        $streakDays = $this->currentStreakDays($attempts);

        $summary = $attempts
            ->filter(fn ($a) => $a->question_set_id)
            ->groupBy('question_set_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'question_set_id'      => $first->question_set_id,
                    'name'                  => $first->questionSet->name ?? 'Unknown set',
                    'category_name'         => $first->questionSet->category->name ?? null,
                    'attempts_count'        => $group->count(),
                    'best_score_percentage' => (float) $group->max('percentage'),
                    'last_attempted_at'     => $group->max('completed_at'),
                ];
            })
            ->sortByDesc('last_attempted_at')
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_attempts'      => $attempts->count(),
                'perfect_scores'      => $perfectScores,
                'high_scores'         => $highScores,
                'distinct_categories' => $distinctCategories,
                'current_streak_days' => $streakDays,
                'quick_learner_count' => $quickLearnerCount,
                'badges' => [
                    [
                        'id'       => 'quick_learner',
                        'progress' => min(100, (int) round($quickLearnerCount / self::QUICK_LEARNER_THRESHOLD * 100)),
                    ],
                    [
                        'id'       => 'quiz_master',
                        'progress' => min(100, (int) round($highScores / self::QUIZ_MASTER_THRESHOLD * 100)),
                    ],
                    [
                        'id'       => 'perfectionist',
                        'progress' => min(100, (int) round($perfectScores / self::PERFECTIONIST_THRESHOLD * 100)),
                    ],
                    [
                        'id'       => 'streak_champion',
                        'progress' => min(100, (int) round($streakDays / self::STREAK_THRESHOLD * 100)),
                    ],
                ],
                'summary' => $summary,
            ],
        ]);
    }

    // Consecutive calendar days (walking back from today) with at least one attempt.
    // Tolerates "hasn't attempted today yet" by also allowing the streak to start yesterday.
    private function currentStreakDays($attempts): int
    {
        $days = $attempts->pluck('completed_at')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        if ($days->isEmpty()) {
            return 0;
        }

        $daySet = $days->flip();
        $todayStr = now()->format('Y-m-d');
        $yesterdayStr = now()->subDay()->format('Y-m-d');

        if (!$daySet->has($todayStr) && !$daySet->has($yesterdayStr)) {
            return 0;
        }

        $cursor = $daySet->has($todayStr) ? now()->startOfDay() : now()->subDay()->startOfDay();
        $streak = 0;

        while ($daySet->has($cursor->format('Y-m-d'))) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }
}
