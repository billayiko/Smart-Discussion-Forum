<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->shareNotifications();
    }

    /**
     * Make the current user's unread notifications available to every view,
     * so the notification bell doesn't need to be wired into each controller.
     */
    protected function shareNotifications(): void
    {
        View::composer('*', function ($view): void {
            $user = auth()->user();

            $view->with('unreadNotifications', $user ? $user->unreadNotifications()->latest()->take(8)->get() : collect());
            $view->with('unreadNotificationsCount', $user ? $user->unreadNotifications()->count() : 0);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
