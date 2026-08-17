<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\QuestionSetPackage;
use App\Services\AccessControlService;

class PackageController extends Controller
{
    public function __construct(private AccessControlService $accessControl)
    {
    }

    // Packages assigned directly to this category or subcategory
    public function index($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $user = auth('sanctum')->user();

        $packages = QuestionSetPackage::with('priceTier')
            ->withCount('questionSets')
            ->where('is_active', true)
            ->where(function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId)->orWhere('subcategory_id', $categoryId);
            })
            ->get()
            ->map(fn (QuestionSetPackage $package) => $this->presentPackage($package, $user));

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'packages' => $packages,
            ],
        ]);
    }

    public function show($id)
    {
        $package = QuestionSetPackage::with(['priceTier', 'category', 'subcategory', 'questionSets' => function ($query) {
            $query->where('is_active', true)->withCount('questions');
        }])->findOrFail($id);

        $user = auth('sanctum')->user();

        return response()->json([
            'success' => true,
            'data' => [
                'package' => $this->presentPackage($package, $user),
                'question_sets' => $package->questionSets->map(fn ($set) => [
                    'id' => $set->id,
                    'name' => $set->name,
                    'description' => $set->description,
                    'questions_count' => $set->questions_count,
                ]),
            ],
        ]);
    }

    private function presentPackage(QuestionSetPackage $package, $user): array
    {
        $priceTier = $package->priceTier;
        $access = $this->accessControl->previewAccess($user, $package);

        return [
            'id' => $package->id,
            'name' => $package->name,
            'description' => $package->description,
            'category_id' => $package->category_id,
            'subcategory_id' => $package->subcategory_id,
            'is_paid' => (bool) $package->is_paid,
            'price' => $package->is_paid ? ($priceTier ? (float) $priceTier->amount : null) : null,
            'price_tier' => $package->is_paid ? ($priceTier->tier_key ?? null) : null,
            'trial_enabled' => (bool) $package->trial_enabled,
            'trial_type' => $package->trial_type,
            'trial_value' => $package->trial_value,
            'question_sets_count' => $package->question_sets_count ?? $package->questionSets()->count(),
            // "owned" excludes an active trial - a trial-only user still needs the
            // separate Free Trial button, not the post-purchase Start button.
            'is_owned' => $access['allowed'] && $access['reason'] !== 'trial',
            'trial_available' => $access['reason'] === 'trial',
        ];
    }
}
