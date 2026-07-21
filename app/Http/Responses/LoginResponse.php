<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToRoleDashboard;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    use RedirectsToRoleDashboard;

    public function toResponse($request): Response
    {
        $redirect = $this->redirectPathForAuthenticatedUser($request);

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : redirect()->intended($redirect);
    }
}
