<?php

use App\Http\Controllers\Api\AlatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PeminjamanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute API SIPINLAB
|--------------------------------------------------------------------------
| Semua rute di berkas ini otomatis berawalan /api.
*/

// Rute publik
Route::get('/', fn () => response()->json([
    'aplikasi' => 'SIPINLAB API',
    'versi' => '1.0.0',
    'waktu_server' => now()->toIso8601String(),
]));

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Rute yang memerlukan token (header Authorization: Bearer <token>)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', DashboardController::class);

    Route::apiResource('kategori', KategoriController::class)
        ->parameters(['kategori' => 'kategori']);
    Route::apiResource('alat', AlatController::class)
        ->parameters(['alat' => 'alat']);

    Route::get('/peminjaman', [PeminjamanController::class, 'index']);
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);
    Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);
    Route::patch('/peminjaman/{peminjaman}/kembali', [PeminjamanController::class, 'kembali']);
});
