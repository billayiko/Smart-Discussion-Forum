<?php

namespace App\Providers;

use App\Models\Quiz;
use App\Notifications\MembershipBlacklisted;
use App\Notifications\MembershipWarning;
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
        $this->shareQuizWatch();
        $this->shareWarningPopup();
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
     * Share a student's upcoming targeted quizzes with every view, so the
     * "quiz pops up" behavior works from any page the student is sitting
     * on, not just the dashboard.
     */
    protected function shareQuizWatch(): void
    {
        View::composer('*', function ($view): void {
            $user = auth()->user();

            $quizzes = ($user && $user->role === 'student')
                ? Quiz::upcomingFor($user)->map(fn (Quiz $quiz) => [
                    'url' => route('quizzes.take', $quiz),
                    'startsAt' => $quiz->scheduled_at->toIso8601String(),
                ])->values()
                : collect();

            $view->with('quizWatchForJs', $quizzes);
        });
    }

    /**
     * Share the current user's most recent unread membership warning or
     * suspension notice (if any) with every view, so it can interrupt
     * whatever page they're on with a popup instead of waiting to be
     * noticed in the bell dropdown.
     */
    protected function shareWarningPopup(): void
    {
        View::composer('*', function ($view): void {
            $user = auth()->user();

            $notification = $user
                ? $user->unreadNotifications()
                    ->whereIn('type', [MembershipWarning::class, MembershipBlacklisted::class])
                    ->latest()
                    ->first()
                : null;

            $view->with('warningPopup', $notification ? [
                'id' => $notification->id,
                'title' => $notification->type === MembershipBlacklisted::class ? 'Account suspended' : 'Inactivity warning',
                'message' => $notification->data['message'] ?? 'You have a new membership notice.',
            ] : null);
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
