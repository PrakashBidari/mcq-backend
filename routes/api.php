<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AppPageController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']); // NEW
Route::post('/verify-reset-otp', [AuthController::class, 'verifyResetOtp']); // NEW
Route::post('/reset-password', [AuthController::class, 'resetPassword']); // NEW

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

     // Profile endpoints
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/update-profile-image', [AuthController::class, 'updateProfileImage']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
});


// Quiz routes

// Public routes
Route::get('/categories', [QuizController::class, 'getCategories']);
Route::get('/categories/{categoryId}/subcategories', [QuizController::class, 'getSubcategories']);
Route::get('/categories/{categoryId}/question-sets', [QuizController::class, 'getQuestionSetsByCategory']);
Route::get('/categories/{categoryId}/packages', [PackageController::class, 'index']);
Route::get('/packages/{id}', [PackageController::class, 'show']);
Route::get('/question-set/{setId}', [QuizController::class, 'getQuestionSet']);
Route::post('/categories/{categoryId}/random-questions', [QuizController::class, 'getRandomQuestionsFromCategory']);
Route::get('/books', [QuizController::class, 'getBooks']);
Route::get('/book-categories', [QuizController::class, 'getBookCategories']);
Route::get('/blogs', [QuizController::class, 'getBlogs']);
Route::get('/blog-categories', [QuizController::class, 'getBlogCategories']);
Route::get('/faqs', [QuizController::class, 'getFaqs']);
Route::get('/ads', [QuizController::class, 'getAds']);
Route::get('/banners', [QuizController::class, 'getBanners']);
Route::get('/app-pages/{slug}', [AppPageController::class, 'show']);
Route::get('/price-tiers', [PurchaseController::class, 'priceTiers']);
Route::get('/attempt-packs', [WalletController::class, 'attemptPacks']);
Route::get('/subscription-plans', [WalletController::class, 'subscriptionPlans']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/quiz/save-attempt', [QuizController::class, 'saveQuizAttempt']);
    Route::get('/quiz/history', [QuizController::class, 'getUserQuizHistory']);
    Route::get('/quiz/achievements', [AchievementController::class, 'index']);

    Route::post('/purchases/verify', [PurchaseController::class, 'verify']);
    Route::get('/purchases/mine', [PurchaseController::class, 'myPurchases']);

    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::get('/subscriptions/mine', [WalletController::class, 'mySubscription']);
});


// Contact Routes (Public)
Route::get('/contact/settings', [ContactController::class, 'getSettings']);
Route::post('/contact/submit', [ContactController::class, 'submit']);
Route::get('/search/question-sets', [QuizController::class, 'searchQuestionSets']);
