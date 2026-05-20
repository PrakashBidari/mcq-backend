<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryManagementController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\ContactSettingController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionSetController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
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

    Route::resource('blogs', BlogController::class);

    Route::resource('faqs', FaqController::class);

    // Users Management (Admin only)
    Route::resource('users', UserController::class);

    // Permissions Management (Admin only)
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('permissions/{user}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('permissions/{user}', [PermissionController::class, 'update'])->name('permissions.update');
});
