<?php

use App\Http\Controllers\HomeController;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Locale-prefixed routes
|--------------------------------------------------------------------------
|
| Every public page lives under /{locale}/. A bare / redirects to the default
| locale rather than serving content, so there is exactly one canonical URL
| per page per language and no duplicate-content ambiguity for search engines.
|
| The redirect is 302, not 301: the day we add locale detection from
| Accept-Language, a cached permanent redirect on every visitor's machine would
| be very hard to undo.
|
*/

Route::redirect('/', '/'.Locales::DEFAULT);

Route::prefix('{locale}')
    ->middleware('locale')
    ->group(function (): void {
        Route::get('/', [HomeController::class, '__invoke'])->name('home');
    });
