<?php

use Grafite\Cms\Controllers\AssetController;
use Grafite\Cms\Controllers\BlogController;
use Grafite\Cms\Controllers\DashboardController;
use Grafite\Cms\Controllers\EventController;
use Grafite\Cms\Controllers\FAQController;
use Grafite\Cms\Controllers\FilesController;
use Grafite\Cms\Controllers\GrafiteCmsFeatureController;
use Grafite\Cms\Controllers\HelpController;
use Grafite\Cms\Controllers\ImagesController;
use Grafite\Cms\Controllers\LinksController;
use Grafite\Cms\Controllers\MenuController;
use Grafite\Cms\Controllers\PagesController;
use Grafite\Cms\Controllers\PromotionsController;
use Grafite\Cms\Controllers\RssController;
use Grafite\Cms\Controllers\SiteMapController;
use Grafite\Cms\Controllers\WidgetsController;
use Illuminate\Support\Facades\Route;

$routePrefix = config('cms.backend-route-prefix', 'cms');

    Route::group(['middleware' => 'web'], function () use ($routePrefix) {
        Route::get($routePrefix, [GrafiteCmsFeatureController::class, 'sendHome']);
        Route::get('{module}/rss', [RssController::class, 'index']);
        Route::get('site-map', [SiteMapController::class, 'index']);
        Route::get($routePrefix.'/hero-images/delete/{entity}/{entity_id}', [GrafiteCmsFeatureController::class, 'deleteHero']);

        /*
        |--------------------------------------------------------------------------
        | Set Language
        |--------------------------------------------------------------------------
        */

        Route::get($routePrefix.'/language/set/{language}', [GrafiteCmsFeatureController::class, 'setLanguage']);

        /*
        |--------------------------------------------------------------------------
        | Public Routes
        |--------------------------------------------------------------------------
        */

        Route::get('public-preview/{encFileName}', [AssetController::class, 'asPreview']);
        Route::get('public-asset/{encFileName}', [AssetController::class, 'asPublic']);
        Route::get('public-download/{encFileName}/{encRealFileName}', [AssetController::class, 'asDownload']);

        /*
         * --------------------------------------------------------------------------
         * Internal APIs
         * --------------------------------------------------------------------------
        */
        Route::group(['middleware' => 'auth'], function () use ($routePrefix) {
            Route::group(['prefix' => 'cms/api'], function () {
                Route::get('images/list', [ImagesController::class, 'apiList']);
                Route::post('images/store', [ImagesController::class, 'apiStore']);
                Route::get('files/list', [FilesController::class, 'apiList']);
            });

            Route::group(['prefix' => $routePrefix], function () {
                Route::get('images/bulk-delete/{ids}', [ImagesController::class, 'bulkDelete']);
                Route::post('images/upload', [ImagesController::class, 'upload']);
                Route::post('files/upload', [FilesController::class, 'upload']);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Cms
        |--------------------------------------------------------------------------
        */

        Route::group(['prefix' => $routePrefix], function () use ($routePrefix) {
            Route::get('asset/{path}/{contentType}', [AssetController::class, 'asset']);

            Route::group(['middleware' => ['auth', 'cms']], function () use ($routePrefix) {
                Route::get('dashboard', [DashboardController::class, 'main']);
                Route::get('help', [HelpController::class, 'main']);

                /*
                |--------------------------------------------------------------------------
                | Common Features
                |--------------------------------------------------------------------------
                */

                Route::get('preview/{entity}/{entityId}', [GrafiteCmsFeatureController::class, 'preview']);
                Route::get('rollback/{entity}/{entityId}', [GrafiteCmsFeatureController::class, 'rollback']);
                Route::get('revert/{id}', [GrafiteCmsFeatureController::class, 'revert']);

                /*
                |--------------------------------------------------------------------------
                | Menus
                |--------------------------------------------------------------------------
                */

                Route::resource('menus', MenuController::class, ['except' => ['show'], 'as' => $routePrefix]);
                Route::post('menus/search', [MenuController::class, 'search']);
                Route::put('menus/{id}/order', [MenuController::class, 'setOrder']);

                /*
                |--------------------------------------------------------------------------
                | Links
                |--------------------------------------------------------------------------
                */

                Route::resource('links', LinksController::class, ['except' => ['index', 'show'], 'as' => $routePrefix]);
                Route::post('links/search', [LinksController::class, 'search']);

                /*
                |--------------------------------------------------------------------------
                | Images
                |--------------------------------------------------------------------------
                */

                Route::resource('images', ImagesController::class, ['as' => $routePrefix, 'except' => ['show']]);
                Route::post('images/search', [ImagesController::class, 'search']);

                /*
                |--------------------------------------------------------------------------
                | Blog
                |--------------------------------------------------------------------------
                */

                Route::resource('blog', BlogController::class, ['as' => $routePrefix, 'except' => ['show']]);
                Route::post('blog/search', [BlogController::class, 'search']);
                Route::get('blog/{id}/history', [BlogController::class, 'history']);

                /*
                |--------------------------------------------------------------------------
                | Pages
                |--------------------------------------------------------------------------
                */

                Route::resource('pages', PagesController::class, ['as' => $routePrefix, 'except' => ['show']]);
                Route::post('pages/search', [PagesController::class, 'search']);
                Route::get('pages/{id}/history', [PagesController::class, 'history']);

                /*
                |--------------------------------------------------------------------------
                | Widgets
                |--------------------------------------------------------------------------
                */

                Route::resource('widgets', WidgetsController::class, ['as' => $routePrefix, 'except' => ['show']]);
                Route::post('widgets/search', [WidgetsController::class, 'search']);

                /*
                |--------------------------------------------------------------------------
                | Promotions
                |--------------------------------------------------------------------------
                */

                Route::resource('promotions', PromotionsController::class, ['as' => $routePrefix, 'except' => ['show']]);
                Route::post('promotions/search', [PromotionsController::class, 'search']);

                /*
                |--------------------------------------------------------------------------
                | FAQs
                |--------------------------------------------------------------------------
                */

                Route::resource('faqs', FAQController::class, ['as' => $routePrefix, 'except' => ['show']]);
                Route::post('faqs/search', [FAQController::class, 'search']);

                /*
                |--------------------------------------------------------------------------
                | Events
                |--------------------------------------------------------------------------
                */

                Route::resource('events', EventController::class, ['as' => $routePrefix, 'except' => ['show']]);
                Route::post('events/search', [EventController::class, 'search']);
                Route::get('events/{id}/history', [EventController::class, 'history']);

                /*
                |--------------------------------------------------------------------------
                | Files
                |--------------------------------------------------------------------------
                */

                Route::get('files/remove/{id}', [FilesController::class, 'remove']);
                Route::post('files/search', [FilesController::class, 'search']);

                Route::resource('files', FilesController::class, ['as' => $routePrefix, 'except' => ['show']]);
            });
        });
    });
