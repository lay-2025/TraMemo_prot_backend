<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// API test
// Route::get('/list', function () {
//     info('backend call api');
//     return response(
//         [
//             'users' => [
//                 [
//                     'id' => '1',
//                     'name' => 'ほげほげ太郎1',
//                     'role' => 'user'
//                 ],
//                 [
//                     'id' => '2',
//                     'name' => 'ほげほげ太郎2',
//                     'role' => 'user'
//                 ],
//             ]
//         ]
//     );
// });

Route::middleware(['clerk.auth'])->group(function () {
    Route::get('/user', function (Request $request) {
        // ClerkユーザーIDを取得
        $clerkUserId = $request->attributes->get('clerk_user_id');
        // 必要に応じてusersテーブルから情報取得・返却
        return response()->json(['clerk_user_id' => $clerkUserId]);
    });
    // 他の認証必須API
    Route::post('/travels/create', [\App\Http\Controllers\Api\TravelController::class, 'store']);
});

Route::get('/travels/{id}', [\App\Http\Controllers\Api\TravelController::class, 'show']);
// Route::post('/travels/create', [\App\Http\Controllers\Api\TravelController::class, 'store']);
