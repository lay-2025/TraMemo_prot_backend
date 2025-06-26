<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API test
Route::get('/list', function () {
    info('backend call api');
    return response(
        [
            'users' => [
                [
                    'id' => '1',
                    'name' => 'ほげほげ太郎1',
                    'role' => 'user'
                ],
                [
                    'id' => '2',
                    'name' => 'ほげほげ太郎2',
                    'role' => 'user'
                ],
            ]
        ]
    );
});

Route::get('/travels/{id}', [\App\Http\Controllers\Api\TravelController::class, 'show']);
Route::post('/travels/create', [\App\Http\Controllers\Api\TravelController::class, 'store']);
