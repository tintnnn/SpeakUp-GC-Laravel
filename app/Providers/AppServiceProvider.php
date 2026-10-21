<?php

namespace App\Providers;

use App\Models\Complaint;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('pages.complainant.dashboard', function ($view) {
            if (! auth()->check()) {
                return;
            }
            $complaints = Complaint::query()
                ->where('complainant_id', auth()->id())
                ->where('is_deleted', false)
                ->latest()
                ->get();
            $view->with('complaints', $complaints);
            $view->with('notifications', auth()->user()->appNotifications()->latest('created_at')->limit(8)->get());
        });

        View::composer('pages.complainant.notifications', function ($view) {
            if (! auth()->check()) {
                return;
            }
            $view->with('notifications', auth()->user()->appNotifications()->latest('created_at')->get());
        });
    }
}
