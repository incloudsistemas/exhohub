<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| OPTIMIZE
|--------------------------------------------------------------------------
|
*/

Route::get('/app-optimize', function () {
    $configCache = Artisan::call('config:cache');
    echo "Configuration cache created! <br/>";

    $eventCache = Artisan::call('event:cache');
    echo "Event cache created! <br/>";

    $routeCache = Artisan::call('route:cache');
    echo "Route cache created! <br/>";

    $viewCache = Artisan::call('view:cache');
    echo "Compiled views cache created! <br/>";

    $optimize = Artisan::call('optimize');
    echo "Optimization files created! <br/>";

    // $iconsCache = Artisan::call('icons:cache');
    // echo "Icons cache created! <br/>";

    // This feature should be enabled only in production.
    // $filamentComponentsCache = Artisan::call('filament:cache-components');
    // echo "Filament Components cache created! <br/>";

    echo "App optimized! <br/>";
});

/*
|--------------------------------------------------------------------------
| CLEAR
|--------------------------------------------------------------------------
|
*/

Route::get('/app-clear', function () {
    $optimizeClear = Artisan::call('optimize:clear');
    echo "Optimize cache cleared! <br/>";

    // This feature should be enabled only in production.
    // $filamentComponentsCacheClear = Artisan::call('filament:clear-cached-components');
    // echo "Filament components cache cleared! <br/>";

    // $responseCacheClear = Artisan::call('responsecache:clear');
    // echo "Response cache cleared! <br/>";

    echo "App cleared! <br/>";
});
