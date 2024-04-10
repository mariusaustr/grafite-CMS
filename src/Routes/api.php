<?php

use Grafite\Cms\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

$routePrefix = config('cms.backend-route-prefix', 'cms');

Route::group(['middleware' => 'web'], function () use ($routePrefix) {
    /*
    |--------------------------------------------------------------------------
    | APIs
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix' => $routePrefix.'/api'], function () {
        Route::group(['middleware' => ['cms-api']], function () {
            Route::get('blog', [ApiController::class, 'all']);
            Route::get('blog/{id}', [ApiController::class, 'find']);

            Route::get('events', [ApiController::class, 'all']);
            Route::get('events/{id}', [ApiController::class, 'find']);

            Route::get('faqs', [ApiController::class, 'all']);
            Route::get('faqs/{id}', [ApiController::class, 'find']);

            Route::get('files', [ApiController::class, 'all']);
            Route::get('files/{id}', [ApiController::class, 'find']);

            Route::get('images', [ApiController::class, 'all']);
            Route::get('images/{id}', [ApiController::class, 'find']);

            Route::get('pages', [ApiController::class, 'all']);
            Route::get('pages/{id}', [ApiController::class, 'find']);

            Route::get('widgets', [ApiController::class, 'all']);
            Route::get('widgets/{id}', [ApiController::class, 'find']);
        });
    });
});
