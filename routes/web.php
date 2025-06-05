<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// API routes for sensor data
Route::middleware('auth')->group(function () {
    Route::get('/api/sensor-data', [DashboardController::class, 'getSensorData'])->name('api.sensor-data');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public API routes for IoT devices
Route::prefix('api/v1')->group(function () {
    Route::post('/sensor/store', [SensorController::class, 'storeWithApiKey'])->name('api.sensor.store.key');
    Route::post('/sensor/data', [SensorController::class, 'store'])->name('api.sensor.store');
    Route::get('/sensor/latest', [SensorController::class, 'latest'])->name('api.sensor.latest');
    Route::get('/sensor/stats', [SensorController::class, 'statistics'])->name('api.sensor.stats');
});

require __DIR__.'/auth.php';
