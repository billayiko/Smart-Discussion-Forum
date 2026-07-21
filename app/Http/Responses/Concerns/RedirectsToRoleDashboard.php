<?php

namespace App\Http\Responses\Concerns;

use Illuminate\Http\Request;

trait RedirectsToRoleDashboard
{
    protected function redirectPathForAuthenticatedUser(Request $request): string
    {
        $user = $request->user();

        abort_if(! $user, 403);

        return route($user->dashboardRouteName(), absolute: false);
    }
}
