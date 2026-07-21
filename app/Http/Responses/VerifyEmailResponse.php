<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToRoleDashboard;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    use RedirectsToRoleDashboard;

    public function toResponse($request): Response
    {
        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended($this->redirectPathForAuthenticatedUser($request).'?verified=1');
    }
}
