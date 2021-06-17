<?php

use App\Http\Controllers\AdsAccountController;
use App\Http\Controllers\DailyAdsDataController;
use App\Http\Controllers\HourlyAdsDataController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/ads-accounts/fetch', [AdsAccountController::class, 'fetch'])->name('ads-accounts.fetch');
Route::post('/ads-accounts/savehourlyfromcsv', [AdsAccountController::class, 'savehourlyfromcsv'])->name('ads-accounts.savehourlyfromcsv');
Route::post('/ads-accounts/savedailyfromcsv', [AdsAccountController::class, 'savedailyfromcsv'])->name('ads-accounts.savedailyfromcsv');
Route::resources([
    'ads-accounts' => AdsAccountController::class,
]);
Route::resources([
    'daily-ads' => DailyAdsDataController::class,
]);
Route::resources([
    'hourly-ads' => HourlyAdsDataController::class,
]);
