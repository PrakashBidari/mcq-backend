<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\UserQuizAttempt;
use App\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    public function __construct(private AccessControlService $accessControl)
    {
    }

    // Get top-level categories (exam types, e.g. SSW / JLPT) with question set counts
    public function getCategories()
    {
        $categories = Category::whereNull('parent_id')
            ->withCount('children')
            ->get()
            ->each(function ($category) {
                $category->has_children = $category->children_count > 0;
            });

        // Question sets live on leaf subcategories, not necessarily directly on the
        // top-level category (e.g. SSW/JLPT have 0 sets of their own - their sets all
        // belong to their subcategories) - so a parent's displayed counts must roll up
        // its children's counts too, not just count $category->questionSets directly.
        $counts = QuestionSet::where('is_active', true)
            ->selectRaw('category_id, COUNT(*) as total, SUM(is_paid = 0) as free, SUM(is_paid = 1) as paid')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $childrenByParent = Category::whereNotNull('parent_id')->get(['id', 'parent_id'])->groupBy('parent_id');

        $categories->each(function ($category) use ($counts, $childrenByParent) {
            $categoryIds = collect([$category->id])
                ->merge($childrenByParent->get($category->id, collect())->pluck('id'));

            $category->question_sets_count = (int) $categoryIds->sum(fn ($id) => $counts->get($id)?->total ?? 0);
            $category->free_sets_count     = (int) $categoryIds->sum(fn ($id) => $counts->get($id)?->free ?? 0);
            $category->paid_sets_count     = (int) $categoryIds->sum(fn ($id) => $counts->get($id)?->paid ?? 0);
        });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Get subcategories of a top-level category (e.g. SSW -> Hotel/Restaurant/...)
    public function getSubcategories($categoryId)
    {
        $category = Category::findOrFail($categoryId);

        $subcategories = Category::where('parent_id', $categoryId)
            ->withCount([
                'questionSets',
                'questionSets as free_sets_count' => function ($query) {
                    $query->where('is_paid', false)->where('is_active', true);
                },
                'questionSets as paid_sets_count' => function ($query) {
                    $query->where('is_paid', true)->where('is_active', true);
                },
                'packages as packages_count' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category'      => $category,
                'subcategories' => $subcategories,
            ],
        ]);
    }

    // Get standalone (non-packaged) question sets by category/subcategory
    public function getQuestionSetsByCategory($categoryId)
    {
        $category = Category::with(['questionSets' => function ($query) {
            $query->where('is_active', true)
                ->whereNull('package_id')
                ->with(['priceTier', 'category'])
                ->withCount('questions');
        }])->findOrFail($categoryId);

        $user = auth('sanctum')->user();

        $questionSets = $category->questionSets->map(function ($set) use ($user) {
            return $this->presentQuestionSet($set, $user);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'category'      => $category,
                'question_sets' => $questionSets,
            ]
        ]);
    }

    // Get questions from a specific question set
    public function getQuestionSet($setId)
    {
        $questionSet = QuestionSet::with([
            'questions' => function ($query) {
                $query->with('options')
                    ->reorder() // ← clears default pivot_order
                    ->orderByRaw('questions.position IS NULL, questions.position ASC, questions.id DESC');
            },
            'category',
            'package',
        ])->where('is_active', true)->findOrFail($setId);

        $user = auth('sanctum')->user();

        $access = $this->accessControl->resolveAccess($user, $questionSet);

        if (!$access['allowed']) {
            return response()->json([
                'success' => false,
                'reason'  => $access['reason'],
                'message' => 'This question set must be purchased before it can be accessed.',
                'data'    => $access['paywall'] ?? $this->purchaseRequiredPayload($questionSet),
            ], 403);
        }

        $questions = $questionSet->questions->map(function ($question) use ($questionSet) {
            return [
                'id'            => $question->id,
                'position'      => $question->position,   // ← add
                'question'      => $question->question,
                'options'       => $question->options->pluck('option_text')->toArray(),
                'correctAnswer' => (int) $question->correct_answer,
                'category'      => $questionSet->category->name,
                'difficulty'    => $question->difficulty,
                'explanation'   => $question->explanation ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'set' => [
                    'id'          => $questionSet->id,
                    'name'        => $questionSet->name,
                    'description' => $questionSet->description,
                    'category'    => $questionSet->category->name,
                    'is_paid'     => (bool) $questionSet->is_paid,
                    'price'       => $questionSet->is_paid ? (float) $questionSet->price : null,
                    'time_limit'  => $questionSet->time_limit,   // ← add
                ],
                'questions' => $questions
            ]
        ]);
    }

    private function purchaseRequiredPayload(QuestionSet $questionSet): array
    {
        $bundleId = config('price_tiers.bundle_id');

        return [
            'question_set_id'    => $questionSet->id,
            'price'              => (float) $questionSet->price,
            'price_tier'         => $questionSet->price_tier,
            'ios_product_id'     => $questionSet->price_tier ? $bundleId . '.' . $questionSet->price_tier : null,
            'android_product_id' => $questionSet->price_tier,
        ];
    }

    private function presentQuestionSet(QuestionSet $set, $user): array
    {
        $priceTier = $set->priceTier;
        $access = $this->accessControl->previewAccess($user, $set);

        return [
            'id'              => $set->id,
            'name'            => $set->name,
            'description'     => $set->description ?? null,
            'category'        => $set->category->name ?? null,
            'is_active'       => $set->is_active,
            'is_paid'         => (bool) $set->is_paid,
            'price'           => $set->is_paid ? (float) ($priceTier->amount ?? $set->price) : null,
            'price_tier'      => $set->is_paid ? ($priceTier->tier_key ?? $set->price_tier) : null,
            'trial_enabled'   => (bool) $set->trial_enabled,
            'trial_type'      => $set->trial_type,
            'trial_value'     => $set->trial_value,
            // "owned" excludes an active trial - a trial-only user still needs the
            // separate Free Trial button, not the post-purchase Start button.
            'is_owned'        => $access['allowed'] && $access['reason'] !== 'trial',
            'trial_available' => $access['reason'] === 'trial',
            'questions_count' => $set->questions_count ?? $set->questions()->count(),
        ];
    }

    // public function getQuestionSet($setId)
    // {
    //     $questionSet = QuestionSet::with(['questions.options', 'category'])
    //         ->where('is_active', true)
    //         ->findOrFail($setId);

    //     $questions = $questionSet->questions->map(function ($question) use ($questionSet) {
    //         return [
    //             'id'            => $question->id,
    //             'question'      => $question->question,
    //             'options'       => $question->options->pluck('option_text')->toArray(),
    //             'correctAnswer' => (int) $question->correct_answer,
    //             'category'      => $questionSet->category->name,
    //             'difficulty'    => $question->difficulty,
    //             'explanation'   => $question->explanation ?? '',
    //         ];
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'set' => [
    //                 'id'          => $questionSet->id,
    //                 'name'        => $questionSet->name,
    //                 'description' => $questionSet->description,
    //                 'category'    => $questionSet->category->name,
    //                 'is_paid'     => (bool) $questionSet->is_paid,
    //                 'price'       => $questionSet->is_paid ? (float) $questionSet->price : null,
    //             ],
    //             'questions' => $questions
    //         ]
    //     ]);
    // }

    // Get random questions from the FREE, standalone (non-packaged) sets in a category -
    // this is the "Free Question Set Quiz" card, deliberately scoped to structurally free
    // content only (not owned/trial/subscription-unlocked paid sets), so no auth or
    // per-user access resolution is needed here - matches the same scope
    // (is_paid = false, no package_id) as the single-sets list the count is shown against.
    public function getRandomQuestionsFromCategory(Request $request, $categoryId)
    {
        $validator = Validator::make($request->all(), [
            'count' => 'required|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $category = Category::findOrFail($categoryId);

        $questionIds = QuestionSet::where('category_id', $categoryId)
            ->where('is_active', true)
            ->where('is_paid', false)
            ->whereNull('package_id')
            ->with('questions')
            ->get()
            ->pluck('questions')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();

        if (empty($questionIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No questions available in this category'
            ], 404);
        }

        $questions = Question::with(['options'])
            ->whereIn('id', $questionIds)
            ->inRandomOrder()
            ->limit($request->count)
            ->get()
            ->map(function ($question) use ($category) {
                return [
                    'id'            => $question->id,
                    'question'      => $question->question,
                    'options'       => $question->options->pluck('option_text')->toArray(),
                    'correctAnswer' => (int) $question->correct_answer,
                    'category'      => $category->name,
                    'difficulty'    => $question->difficulty,
                    'explanation'   => $question->explanation ?? '',
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $questions
        ]);
    }

    // Save quiz attempt
    public function saveQuizAttempt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_set_id' => 'nullable|exists:question_sets,id',
            'score'           => 'required|integer|min:0',
            'total_questions' => 'required|integer|min:1',
            'answers'         => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $percentage = ($request->score / $request->total_questions) * 100;

        $attempt = UserQuizAttempt::create([
            'user_id'          => $request->user()->id,
            'question_set_id'  => $request->question_set_id,
            'score'            => $request->score,
            'total_questions'  => $request->total_questions,
            'percentage'       => $percentage,
            'answers'          => $request->answers,
            'completed_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz attempt saved successfully',
            'data'    => $attempt
        ]);
    }

    // Get user quiz history
    public function getUserQuizHistory(Request $request)
    {
        $attempts = UserQuizAttempt::with(['questionSet.category'])
            ->where('user_id', $request->user()->id)
            ->orderBy('completed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $attempts
        ]);
    }

    public function getBooks()
    {
        $books = Book::with('category')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($book) {
                return [
                    'id'          => $book->id,
                    'title'       => $book->title,
                    'author'      => $book->author,
                    'description' => $book->description,
                    'cover'       => $book->image
                        ? url('storage/' . $book->image)
                        : $book->cover,
                    'rating'      => (float) $book->rating,
                    'pages'       => $book->pages,
                    'duration'    => $book->duration,
                    'categoryId'  => $book->category_id,
                    'category'    => $book->category->name,
                    'difficulty'  => $book->difficulty,
                    'students'    => $book->students,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $books
        ]);
    }

    public function getBlogs()
    {
        $blogs = Blog::with('blogCategory')
            ->where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(function ($blog) {
                return [
                    'id'          => $blog->id,
                    'title'       => $blog->title,
                    'slug'        => $blog->slug,
                    'excerpt'     => $blog->excerpt,
                    'content'     => $blog->content,
                    'image'       => $blog->image
                        ? url('storage/' . $blog->image)
                        : $blog->cover_url,
                    'category'    => $blog->blogCategory->name ?? $blog->category,
                    'categoryId'  => $blog->blog_category_id,
                    'author'      => $blog->author,
                    'readTime'    => $blog->read_time,
                    'likes'       => $blog->likes,
                    'views'       => $blog->views,
                    'publishedAt' => $blog->published_at ? $blog->published_at->format('M d, Y') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $blogs
        ]);
    }

    public function getFaqs()
    {
        $faqs = Faq::with('category')
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($faq) {
                return [
                    'id'         => $faq->id,
                    'question'   => $faq->question,
                    'answer'     => $faq->answer,
                    'category'   => $faq->category->name,
                    'categoryId' => $faq->category_id,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $faqs
        ]);
    }

    public function getAds()
    {
        $ads = Advertisement::where('is_active', true)
            ->get()
            ->map(function ($ad) {
                return [
                    'position'    => $ad->position,
                    'title'       => $ad->title,
                    'description' => $ad->description,
                    'buttonText'  => $ad->button_text,
                    'linkUrl'     => $ad->link_url,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $ads
        ]);
    }

    public function searchQuestionSets(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $user = auth('sanctum')->user();

        $sets = QuestionSet::with(['category', 'priceTier', 'package'])
            ->where('is_active', true)
            ->whereNull('package_id')
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(fn ($set) => $this->presentQuestionSet($set, $user));

        return response()->json(['success' => true, 'data' => $sets]);
    }

    public function getBlogCategories()
    {
        return response()->json([
            'success' => true,
            'data' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function getBookCategories()
    {
        return response()->json([
            'success' => true,
            'data' => BookCategory::orderBy('name')->get(),
        ]);
    }
}
