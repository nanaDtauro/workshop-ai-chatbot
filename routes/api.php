<?php

use App\Http\Controllers\Ai\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/chat/messages', [ChatController::class, 'messages'])
    ->name('chatbot.messages');

Route::delete('/chat/conversations/{id}', [ChatController::class, 'deleteConversation'])
    ->name('chatbot.conversations.single.delete');