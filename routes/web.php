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

// Chat routes
Route::get('chat', [ChatController::class, 'index'])->name('chat');

Route::prefix('api/chat')->group(function () {
    Route::post('send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('history/{sessionId}', [ChatController::class, 'history'])->name('chat.history');
});

require __DIR__.'/settings.php';
