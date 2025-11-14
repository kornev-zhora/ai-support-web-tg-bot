<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Chat routes (no auth required)
Route::get('chat', [ChatController::class, 'index'])->name('chat');

// Privacy policy
Route::get('privacy', function () {
    return Inertia::render('Privacy');
})->name('privacy');

Route::prefix('api/chat')->group(function () {
    Route::post('send', [ChatController::class, 'sendMessage'])
        ->middleware(\App\Http\Middleware\TrackChatStatistics::class)
        ->name('chat.send');
    Route::get('history/{sessionId}', [ChatController::class, 'history'])->name('chat.history');
});

require __DIR__.'/settings.php';
