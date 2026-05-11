<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useTailwind();

        // Share unread notification count with the main layout
        View::composer('layouts.app', function ($view) {
            $count = 0;
            if (Auth::check()) {
                $count = Notification::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->count();
            }
            $view->with('unreadNotificationCount', $count);
        });
    }
}
