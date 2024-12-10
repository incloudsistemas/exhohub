<?php

use App\Http\Controllers\Web\BlogPostController;
use App\Http\Controllers\Web\BusinessLeadController;
use App\Http\Controllers\Web\DefaultPageController;
use App\Http\Controllers\Web\MailUsController;
use App\Http\Controllers\Web\RealEstate\EnterpriseController;
use App\Http\Controllers\Web\RealEstate\IndividualController;
use App\Http\Controllers\Web\RealEstate\PortalIntegrationController;
use App\Http\Controllers\Web\RealEstate\PropertyController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

/*
|--------------------------------------------------------------------------
| PUBLIC WEBSITE ROUTES
|--------------------------------------------------------------------------
|
 */

Route::get('/', [DefaultPageController::class, 'index'])
    ->name('web.pgs.index');

Route::get('quem-somos', [DefaultPageController::class, 'about'])
    ->name('web.pgs.about');

Route::get('fale-conosco', [DefaultPageController::class, 'contactUs'])
    ->name('web.pgs.contact-us');

Route::get('trabalhe-conosco', [DefaultPageController::class, 'workWithUs'])
    ->name('web.pgs.work-with-us');

Route::get('links', [DefaultPageController::class, 'linksTree'])
    ->name('web.pgs.links-tree');

Route::get('regras/{slug}', [DefaultPageController::class, 'rules'])
    ->name('web.pgs.rules');

Route::name('web.mail-us.')
    ->group(function () {
        Route::post('contact-us', [MailUsController::class, 'sendContactUsForm'])
            ->name('contact-us');

        Route::post('work-with-us', [MailUsController::class, 'sendWorkWithUsForm'])
            ->name('work-with-us');

        Route::post('newsletter-subscribe', [MailUsController::class, 'sendNewsletterSubscribeForm'])
            ->name('newsletter-subscribe');
    });

Route::name('web.business.')
    ->group(function () {
        Route::post('lead', [BusinessLeadController::class, 'sendBusinessLeadForm'])
            ->name('lead');

        Route::post('lead-from-canal-pro', [BusinessLeadController::class, 'receiveBusinessLeadFromCanalPro'])
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
            ->name('lead-from-canal-pro');

        Route::post('lead-from-meta-ads', [BusinessLeadController::class, 'receiveBusinessLeadFromMetaAds'])
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
            ->name('lead-from-meta-ads');
    });

Route::name('web.blog.')
    ->prefix('blog')
    ->group(function () {
        Route::get('/', [BlogPostController::class, 'index'])
            ->name('index');

        Route::get('busca', [BlogPostController::class, 'search'])
            ->name('search');

        Route::get('categoria/{category}', [BlogPostController::class, 'indexByCategory'])
            ->name('category');

        Route::get('tipo/{role}', [BlogPostController::class, 'indexByRole'])
            ->name('role');

        Route::get('{slug}', [BlogPostController::class, 'show'])
            ->name('show');
    });

Route::name('web.real-estate.')
    ->group(function () {
        Route::group(['prefix' => 'imoveis'], function () {
            Route::get('busca', [PropertyController::class, 'search'])
                ->name('properties.search');

            Route::get('{slug}/{code}', [PropertyController::class, 'show'])
                ->name('properties.show');

            Route::get('{role}', [IndividualController::class, 'index'])
                ->name('individuals.index');

            Route::get('{role}/tipo/{usage}/{type}', [IndividualController::class, 'indexByUsageType'])
                ->name('individuals.usage-type');

            Route::group(['prefix' => 'integracao/portal'], function () {
                Route::get('canal-pro', [PortalIntegrationController::class, 'publishOnCanalPro'])
                    ->name('publish-on.canal-pro');
            });
        });

        Route::group(['prefix' => 'lancamentos'], function () {
            Route::get('/', [EnterpriseController::class, 'index'])
                ->name('enterprises.index');

            Route::get('estagio/{role}', [EnterpriseController::class, 'indexByRole'])
                ->name('enterprises.role');

            Route::get('tipo/{usage}/{type}', [EnterpriseController::class, 'indexByUsageType'])
                ->name('enterprises.usage-type');
        });
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

    // This feature should be enabled only in production.
    $filamentComponentsCache = Artisan::call('filament:cache-components');
    echo "Filament Components cache created! <br/>";

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
    $filamentComponentsCacheClear = Artisan::call('filament:clear-cached-components');
    echo "Filament components cache cleared! <br/>";

    echo "App cleared! <br/>";
});
