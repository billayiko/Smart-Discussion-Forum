<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToRoleDashboard;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    use RedirectsToRoleDashboard;

    public function toResponse($request): Response
    {
        $redirect = $this->redirectPathForAuthenticatedUser($request);

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 201)
            : redirect()->intended($redirect);
    }
}
