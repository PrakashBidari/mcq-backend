<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AppPageController;
use App\Http\Controllers\AttemptPackController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookCategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryManagementController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\ContactSettingController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PriceTierController;
use App\Http\Controllers\PurchaseAdminController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\QuestionSetPackageController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/storagelink', function () {
    Artisan::call('storage:link');
    return 'Storage link created!';
});

Route::get('/check-reverb', function () {
    if (request('secret') !== env('BUILD_SECRET')) abort(403);
    return response()->json([
        'reverb_in_vendor' => is_dir(base_path('vendor/laravel/reverb')),
        'composer_lock_has_reverb' => str_contains(file_get_contents(base_path('composer.lock')), 'laravel/reverb'),
    ]);
});

Route::get('/clear-all-cache', function () {
    if (request('secret') !== env('BUILD_SECRET')) abort(403);
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return 'all cleared';
});

Route::get('/start-reverb', function () {
    if (request('secret') !== env('BUILD_SECRET')) abort(403);

    exec('pgrep -f "artisan reverb:start"', $running);
    if (!empty($running)) {
        return response()->json(['status' => 'already running', 'pids' => $running]);
    }

    exec('cd ' . base_path() . ' && nohup php artisan reverb:start > storage/logs/reverb.log 2>&1 & echo $!', $output);
    return response()->json(['status' => 'started', 'output' => $output]);
});

Route::get('/check-reverb-port', function () {
    if (request('secret') !== env('BUILD_SECRET')) abort(403);
    exec('netstat -tlnp 2>/dev/null | grep 6001', $output);
    if (empty($output)) {
        exec('ss -tlnp 2>/dev/null | grep 6001', $output);
    }
    return response()->json($output);
});

Route::get('/reverb-log', function () {
    if (request('secret') !== env('BUILD_SECRET')) abort(403);
    $path = storage_path('logs/reverb.log');
    if (!file_exists($path)) return 'no log file';
    return response()->json(['content' => file_get_contents($path)]);
});

Route::get('/package-discover', function () {
    if (request('secret') !== env('BUILD_SECRET')) abort(403);
    Artisan::call('package:discover');
    return Artisan::output();
});

Route::get('/laravel-log', function () {
    if (request('secret') !== env('BUILD_SECRET')) abort(403);
    $path = storage_path('logs/laravel.log');
    if (!file_exists($path)) return 'no log file';

    $content = file_get_contents($path);
    $pos = strrpos($content, '.ERROR:');
    if ($pos === false) return 'no ERROR entries found';

    $snippet = substr($content, $pos, 3000); // first 3000 chars of the last error
    return response('<pre>' . htmlspecialchars($snippet) . '</pre>');
});

// Include auth routes
require __DIR__ . '/auth.php';

// Dashboard routes (protected)
Route::middleware(['auth', 'dashboard'])->prefix('dashboard')->group(function () {

    // Dashboard Home
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Categories CRUD
    Route::resource('categories', CategoryController::class);

    // Question Sets CRUD
    Route::resource('question-sets', QuestionSetController::class);

    Route::resource('contact-settings', ContactSettingController::class);

    // Contact Messages Routes
    Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('contact-messages/{id}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::post('contact-messages/{id}/reply', [ContactMessageController::class, 'reply'])->name('contact-messages.reply');
    Route::delete('contact-messages/{id}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    Route::post('contact-messages/{id}/mark-read', [ContactMessageController::class, 'markAsRead'])->name('contact-messages.mark-read');
    Route::post('contact-messages/{id}/mark-unread', [ContactMessageController::class, 'markAsUnread'])->name('contact-messages.mark-unread');

    // Questions CRUD
    Route::resource('questions', QuestionController::class);

    Route::resource('books', BookController::class);
    Route::resource('book-categories', BookCategoryController::class);

    Route::resource('blogs', BlogController::class);
    Route::resource('blog-categories', BlogCategoryController::class);

    Route::resource('faqs', FaqController::class);

    // Question Set Packages
    Route::resource('packages', QuestionSetPackageController::class);
    Route::get('packages-available-sets', [QuestionSetPackageController::class, 'availableQuestionSets'])->name('packages.available-sets');

    // Dynamic payment catalog
    Route::resource('price-tiers', PriceTierController::class);
    Route::patch('price-tiers/{price_tier}/toggle', [PriceTierController::class, 'toggle'])->name('price-tiers.toggle');
    Route::resource('attempt-packs', AttemptPackController::class);
    Route::resource('subscription-plans', SubscriptionPlanController::class);

    // Purchases / subscribers / revenue (read-only)
    Route::get('purchases', [PurchaseAdminController::class, 'index'])->name('purchases.index');
    Route::get('purchases/grant', [PurchaseAdminController::class, 'grantForm'])->name('purchases.grant-form');
    Route::post('purchases/grant', [PurchaseAdminController::class, 'grant'])->name('purchases.grant');
    Route::get('subscribers', [PurchaseAdminController::class, 'subscribers'])->name('subscribers.index');
    Route::get('revenue', [PurchaseAdminController::class, 'revenue'])->name('revenue.index');

    // Advertisements — edit & active/inactive toggle only (no create/delete)
    Route::get('advertisements', [AdvertisementController::class, 'index'])->name('advertisements.index');
    Route::get('advertisements/{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('advertisements.edit');
    Route::put('advertisements/{advertisement}', [AdvertisementController::class, 'update'])->name('advertisements.update');
    Route::patch('advertisements/{advertisement}/toggle', [AdvertisementController::class, 'toggleStatus'])->name('advertisements.toggle');

    // Home banners (hero swiper slides)
    Route::resource('banners', BannerController::class);

    // App content pages — edit-only, fixed slugs (about-app, privacy-policy)
    Route::get('app-pages/{slug}/edit', [AppPageController::class, 'edit'])->name('app-pages.edit');
    Route::put('app-pages/{slug}', [AppPageController::class, 'update'])->name('app-pages.update');

    // Users Management (Admin only)
    Route::resource('users', UserController::class);

    // Permissions Management (Admin only)
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('permissions/{user}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('permissions/{user}', [PermissionController::class, 'update'])->name('permissions.update');

    // Chat
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{user}/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::get('chat/{user}/media', [ChatController::class, 'media'])->name('chat.media');
    Route::post('chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::get('chat/unread', [ChatController::class, 'unread'])->name('chat.unread');
    Route::delete('chat/message/{message}', [ChatController::class, 'destroy'])->name('chat.message.destroy');
});
