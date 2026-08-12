<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;


Route::prefix("v1")->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        // Authentication
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Posts
        Route::get('/posts', [PostController::class, 'index']);
        Route::get('/posts/{post}', [PostController::class, 'show']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);

        // Comments
        Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
        Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
        Route::put('/posts/{post}/comments/{comment}', [CommentController::class, 'update'])->scopeBindings();
        Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])->scopeBindings();
    });
    Route::middleware(['auth:sanctum', 'throttle:images'])->group(function () {

        Route::post('/posts/{post}/images', [ImageController::class, 'store']);
        Route::delete('/posts/{post}/images/{image}', [ImageController::class, 'destroy'])->scopeBindings();
    });
});