<?php

use App\Http\Controllers\Admin\EventPageController;
use App\Http\Controllers\SurveyPageController;
use Illuminate\Support\Facades\Route;

// Welcome page
Route::view('/', 'welcome')->name('home');

// Fortify auth routes are auto-registered by FortifyServiceProvider

Route::middleware(['auth'])->group(function () {
    Route::redirect('dashboard', '/admin/events');
});

// Admin: Event Management
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('events', [EventPageController::class, 'index'])->name('events');
    Route::get('events/create', [EventPageController::class, 'create'])->name('events.create');
    Route::get('events/{event}', [EventPageController::class, 'show'])->name('events.show');
    Route::get('events/{event}/summary', [EventPageController::class, 'summary'])->name('events.summary');
});

// Public Survey
Route::prefix('s')->name('survey.')->group(function () {
    Route::get('{event}', [SurveyPageController::class, 'chat'])->name('chat');
    Route::get('{event}/booth/{boothId}', [SurveyPageController::class, 'chatWithBooth'])->name('chat.booth');
    Route::get('{event}/complete', [SurveyPageController::class, 'complete'])->name('complete');
    Route::get('{event}/register', [SurveyPageController::class, 'register'])->name('register');
    Route::get('{event}/register/complete', [SurveyPageController::class, 'registerComplete'])->name('register.complete');
});

require __DIR__.'/settings.php';
