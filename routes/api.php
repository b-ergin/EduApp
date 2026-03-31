<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\QuizController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/quizzes', [QuizController::class, 'index']);
        Route::get('/quizzes/{quiz}/start', [QuizController::class, 'start']);
        Route::get('/quizzes/{quiz}/questions/{question}', [QuizController::class, 'showQuestion']);
        Route::post('/questions/{question}/answer', [QuizController::class, 'submitAnswer']);

    });
});
