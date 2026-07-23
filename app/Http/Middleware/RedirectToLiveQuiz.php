<?php

namespace App\Http\Middleware;

use App\Models\Quiz;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToLiveQuiz
{
    /**
     * Routes a student must always be able to reach even while a quiz is
     * live, to avoid redirect loops or blocking their own onboarding gate.
     */
    protected const EXCLUDED_ROUTES = [
        'quizzes.take',
        'quizzes.submit',
        'onboarding.edit',
        'onboarding.update',
        'onboarding.decline',
        'logout',
    ];

    /**
     * Interrupts a student on any page (not just the dashboard) the moment
     * a quiz targeted at them goes live, mirroring the dashboard's own
     * immediate redirect. Only full page loads are affected: POST/PUT/etc.
     * requests and JSON/AJAX calls pass through untouched, so this can't
     * hijack an in-page action like liking a post or sending a message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->role === 'student'
            && $request->isMethod('GET')
            && ! $request->expectsJson()
            && ! in_array($request->route()?->getName(), self::EXCLUDED_ROUTES, true)
        ) {
            $liveQuiz = Quiz::liveFor($user);

            if ($liveQuiz) {
                return redirect()->route('quizzes.take', $liveQuiz);
            }
        }

        return $next($request);
    }
}
