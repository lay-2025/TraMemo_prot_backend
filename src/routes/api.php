<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['clerk.auth'])->group(function () {
    Route::get('/user', function (Request $request) {
        // ClerkユーザーIDを取得
        $clerkUserId = $request->attributes->get('clerk_user_id');
        // 必要に応じてusersテーブルから情報取得・返却
        return response()->json(['clerk_user_id' => $clerkUserId]);
    });

    // health check
    Route::get('/health/auth', [\App\Http\Controllers\Api\HealthCheckController::class, 'auth']);

    // 他の認証必須API
    // Route::post('/travels', [\App\Http\Controllers\Api\TravelController::class, 'store']); // 本番環境でのユーザ認証機能が上手く動作しないため、一時的にコメントアウト
});

Route::get('/travels/{id}', [\App\Http\Controllers\Api\TravelController::class, 'show']);
Route::post('/travels', [\App\Http\Controllers\Api\TravelController::class, 'store']);

// webhook
Route::post('/webhook/clerk', [\App\Http\Controllers\Api\WebhookController::class, 'handleClerk']);

// health check
Route::get('/health/app', [\App\Http\Controllers\Api\HealthCheckController::class, 'app']);
Route::get('/health/db', [\App\Http\Controllers\Api\HealthCheckController::class, 'db']);
Route::get('/health/env', [\App\Http\Controllers\Api\HealthCheckController::class, 'env']);
