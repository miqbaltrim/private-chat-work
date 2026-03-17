<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Chat routes (authenticated)
Route::middleware('auth')->group(function () {
    // Main chat page
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');

    // IMPORTANT: specific routes MUST come before {room} parameter routes
    Route::post('/chat/rooms', [ChatController::class, 'createRoom']);
    Route::post('/chat/upload', [ChatController::class, 'uploadFile']);
    Route::get('/chat/online-users', [ChatController::class, 'getOnlineUsers']);
    Route::get('/chat/search-rooms', [ChatController::class, 'searchRooms']);
    Route::post('/user/public-key', [AuthController::class, 'updatePublicKey']);

    // Room-specific routes (with {room} parameter)
    Route::post('/chat/rooms/{room}/join', [ChatController::class, 'joinRoom']);
    Route::post('/chat/rooms/{room}/leave', [ChatController::class, 'leaveRoom']);
    Route::get('/chat/rooms/{room}/members', [ChatController::class, 'getRoomMembers']);

    // Message routes (with {room} parameter) - LAST
    Route::get('/chat/{room}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/{room}/messages', [ChatController::class, 'sendMessage']);
});