<?php

namespace App\Providers;

use App\Events\CommentCreated;
use App\Listeners\SendCommentNotification;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_AFTER,
            fn(): View => view('connect'),
        );

        // Register event listeners
        Event::listen(
            CommentCreated::class,
            SendCommentNotification::class,
        );
    }
}
