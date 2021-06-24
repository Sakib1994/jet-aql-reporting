<?php

use App\Http\Controllers\AccountsSummarizeController;
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

Route::group(['middleware' => ['auth', 'role:sss|admin']], function () {
    Route::get('/sss-daily-summary/{startDate?}/{endDate?}', [AccountsSummarizeController::class, 'processSSS'])->name('sss-daily-summary');
    Route::get('/sss-detail/{date}', [AccountsSummarizeController::class, 'datewiseExperiment'])->name('sss-detail-exp');
});
Route::group(['middleware' => ['auth', 'role:aql|admin']], function () {
    Route::get('/aql-daily-summary/{startDate?}/{endDate?}', [AccountsSummarizeController::class, 'aqlProcress'])->name('aql-daily-summary');

});
Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/ads-accounts/fetch', [AdsAccountController::class, 'fetch'])->name('ads-accounts.fetch');
    Route::get('/daily-ads/fetchdaily', [DailyAdsDataController::class, 'fetch'])->name('daily-ads.fetchdaily');
    Route::post('/daily-ads/savedailyfromcsv', [DailyAdsDataController::class, 'savedailyfromcsv'])->name('daily-ads.savedailyfromcsv');
    Route::get('/hourly-ads/fetchhourly', [HourlyAdsDataController::class, 'fetch'])->name('hourly-ads.fetchhourly');
    Route::post('/hourly-ads/savehourlyfromcsv', [HourlyAdsDataController::class, 'savehourlyfromcsv'])->name('hourly-ads.savehourlyfromcsv');
    Route::get('/hourly-ads/{date}/{accountId}', [HourlyAdsDataController::class, 'datewise'])->name('datewise-hourly-detail');
    Route::resources([
        'ads-accounts' => AdsAccountController::class,
    ]);
    Route::resources([
        'daily-ads' => DailyAdsDataController::class,
    ]);
    Route::resources([
        'hourly-ads' => HourlyAdsDataController::class,
    ]);
    Route::resources([
        'daily-summary' => AccountsSummarizeController::class,
    ]);
});
