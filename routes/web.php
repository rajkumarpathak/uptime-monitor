<?php

use Illuminate\Support\Facades\Route;
use App\Models\Website;
use App\Services\WebsiteMonitorService;
use App\Jobs\SendWebsiteDownEmailJob;
use App\Http\Controllers\Api\ClientController;


Route::get('/test/db', function () {
    $clients = \App\Models\Client::withCount('websites')->get();
    $websites = \App\Models\Website::limit(5)->get();
    
    return view('test-db', compact('clients', 'websites'));
});

Route::get('/test/monitor', function () {
    \Artisan::call('monitor:check-websites');
    return "Monitor command executed. Check logs at storage/logs/laravel.log";
});

Route::get('/test/queue', function () {
    $jobCount = \Illuminate\Support\Facades\Redis::command('llen', ['queues:default']);
    return "Jobs in queue: $jobCount";
});

// API Routes
Route::prefix('api/v1')->group(function () {
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{client}/websites', [ClientController::class, 'websites']);
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');