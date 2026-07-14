<?php

namespace App\Providers;

use App\Models\Quiz;
use App\Policies\QuizPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Quiz::class => QuizPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
