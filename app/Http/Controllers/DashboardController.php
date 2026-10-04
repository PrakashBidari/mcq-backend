<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Category;
use App\Models\DailyVisit;
use App\Models\PriceTier;
use App\Models\Purchase;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\QuestionSetPackage;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // range => [number of buckets shown, bucket size]
    private const RANGES = [
        'day'   => [30, 'day'],
        'week'  => [12, 'week'],
        'month' => [12, 'month'],
        'year'  => [5, 'year'],
    ];

    public function index()
    {
        $user = auth()->user();
        $can = fn (string $model) => $user->isAdmin() || $user->hasPermission('read', $model);

        $today = now()->toDateString();
        $weekAgo = now()->subDays(6)->toDateString();
        $monthStart = now()->startOfMonth();

        $headline = [
            'users_total'     => User::where('role', 'user')->count(),
            'users_new_month' => User::where('role', 'user')->where('created_at', '>=', $monthStart)->count(),
            'active_today'    => DailyVisit::where('visit_date', $today)->distinct()->count('user_id'),
            'active_week'     => DailyVisit::whereBetween('visit_date', [$weekAgo, $today])->distinct()->count('user_id'),
            'visitors_today'  => DailyVisit::where('visit_date', $today)->count(),
            'visitors_week'   => DailyVisit::whereBetween('visit_date', [$weekAgo, $today])->distinct()->count('visitor_key'),
        ];

        $canSeePayments = $can('Purchase');

        if ($canSeePayments) {
            $completed = Purchase::where('status', 'completed');
            $headline['revenue_total'] = (float) (clone $completed)->sum('price_paid');
            $headline['revenue_month'] = (float) (clone $completed)->where('purchased_at', '>=', $monthStart)->sum('price_paid');
        }

        // One block per area of the panel; each item is hidden unless the user may read it
        $sections = [];

        $quiz = array_filter([
            $can('Category') ? ['label' => 'Categories', 'count' => Category::count(), 'route' => route('categories.index')] : null,
            $can('QuestionSet') ? ['label' => 'Question Sets', 'count' => QuestionSet::count(), 'route' => route('question-sets.index')] : null,
            $can('Question') ? ['label' => 'Questions', 'count' => Question::count(), 'route' => route('questions.index')] : null,
            $can('Package') ? ['label' => 'Packages', 'count' => QuestionSetPackage::count(), 'route' => route('packages.index')] : null,
        ]);
        if ($quiz) {
            $sections[] = ['title' => 'Quiz', 'icon' => 'question', 'tone' => 'purple', 'items' => $quiz];
        }

        $books = array_filter([
            $can('Book') ? ['label' => 'Books', 'count' => Book::count(), 'route' => route('books.index')] : null,
            $can('BookCategory') ? ['label' => 'Book Categories', 'count' => BookCategory::count(), 'route' => route('book-categories.index')] : null,
        ]);
        if ($books) {
            $sections[] = ['title' => 'Books', 'icon' => 'book', 'tone' => 'blue', 'items' => $books];
        }

        $blog = array_filter([
            $can('Blog') ? ['label' => 'Blog Posts', 'count' => Blog::count(), 'route' => route('blogs.index')] : null,
            $can('BlogCategory') ? ['label' => 'Blog Categories', 'count' => BlogCategory::count(), 'route' => route('blog-categories.index')] : null,
        ]);
        if ($blog) {
            $sections[] = ['title' => 'Blog', 'icon' => 'newspaper', 'tone' => 'green', 'items' => $blog];
        }

        $payments = array_filter([
            $canSeePayments ? ['label' => 'Purchases', 'count' => Purchase::where('status', 'completed')->count(), 'route' => route('purchases.index')] : null,
            $canSeePayments ? ['label' => 'Paying Users', 'count' => Purchase::where('status', 'completed')->distinct()->count('user_id'), 'route' => route('subscribers.index')] : null,
            $can('PriceTier') ? ['label' => 'Price Tiers', 'count' => PriceTier::count(), 'route' => route('price-tiers.index')] : null,
        ]);
        if ($payments) {
            $sections[] = ['title' => 'Payments', 'icon' => 'card', 'tone' => 'orange', 'items' => $payments];
        }

        if ($user->isAdmin()) {
            $byRole = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

            $sections[] = ['title' => 'Users & Access', 'icon' => 'users', 'tone' => 'red', 'items' => [
                ['label' => 'App Users', 'count' => (int) ($byRole['user'] ?? 0), 'route' => route('users.index', ['role' => 'user'])],
                ['label' => 'Teachers', 'count' => (int) ($byRole['teacher'] ?? 0), 'route' => route('users.index', ['role' => 'teacher'])],
                ['label' => 'Admins', 'count' => (int) ($byRole['admin'] ?? 0), 'route' => route('users.index', ['role' => 'admin'])],
            ]];
        }

        return view('dashboard', compact('headline', 'sections', 'canSeePayments'));
    }

    // JSON for the dashboard charts: ?range=day|week|month|year
    public function chartData(Request $request)
    {
        $range = array_key_exists($request->query('range'), self::RANGES) ? $request->query('range') : 'day';
        $buckets = $this->buckets($range);

        $from = $buckets[0]['start'];
        $to = end($buckets)['end'];
        $user = auth()->user();

        $newUsers = User::where('role', 'user')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->toBase()
            ->pluck('total', 'day');

        $data = [
            'range'       => $range,
            'labels'      => array_column($buckets, 'label'),
            'newUsers'    => $this->sumIntoBuckets($newUsers, $buckets),
            'activeUsers' => [],
            'visitors'    => [],
            'revenue'     => null,
        ];

        // Unique people can't be added up across days (the same person would be counted
        // once per day), so they are counted per bucket.
        foreach ($buckets as $bucket) {
            $counts = DailyVisit::whereBetween('visit_date', [$bucket['start']->toDateString(), $bucket['end']->toDateString()])
                ->selectRaw('COUNT(DISTINCT visitor_key) as visitors, COUNT(DISTINCT user_id) as active')
                ->toBase()
                ->first();

            $data['visitors'][] = (int) $counts->visitors;
            $data['activeUsers'][] = (int) $counts->active;
        }

        if ($user->isAdmin() || $user->hasPermission('read', 'Purchase')) {
            $revenue = Purchase::where('status', 'completed')
                ->whereBetween('purchased_at', [$from, $to])
                ->selectRaw('DATE(purchased_at) as day, SUM(price_paid) as total')
                ->groupBy('day')
                ->toBase()
                ->pluck('total', 'day');

            $data['revenue'] = $this->sumIntoBuckets($revenue, $buckets);
        }

        return response()->json($data);
    }

    // Consecutive periods ending with the current one, oldest first
    private function buckets(string $range): array
    {
        [$count, $unit] = self::RANGES[$range];
        $buckets = [];

        for ($i = $count - 1; $i >= 0; $i--) {
            $start = match ($unit) {
                'day'   => now()->startOfDay()->subDays($i),
                'week'  => now()->startOfWeek()->subWeeks($i),
                'month' => now()->startOfMonth()->subMonthsNoOverflow($i),
                'year'  => now()->startOfYear()->subYears($i),
            };

            $buckets[] = [
                'start' => $start,
                'end'   => $start->copy()->endOf($unit),
                'label' => match ($unit) {
                    'day', 'week' => $start->format('M j'),
                    'month'       => $start->format('M Y'),
                    'year'        => $start->format('Y'),
                },
            ];
        }

        return $buckets;
    }

    // $daily is keyed by 'Y-m-d'; returns one total per bucket
    private function sumIntoBuckets($daily, array $buckets): array
    {
        $totals = array_fill(0, count($buckets), 0);

        foreach ($daily as $day => $value) {
            foreach ($buckets as $index => $bucket) {
                if ($day >= $bucket['start']->toDateString() && $day <= $bucket['end']->toDateString()) {
                    $totals[$index] += (float) $value;
                    break;
                }
            }
        }

        return $totals;
    }
}
