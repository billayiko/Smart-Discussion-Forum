<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\ResetUserPassword;
use App\Concerns\SecurityQuestionValidationRules;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * A stateless, token-based adaptation of SecurityQuestionPasswordController
 * for the desktop client: the web version verifies identity into the
 * session, then reads that session back on the following request. A
 * Sanctum-token client has no session, so verify() here hands back a
 * short-lived opaque token (cached server-side, same 10-minute TTL as the
 * web session flow) that reset() must present instead.
 */
class PasswordResetController extends Controller
{
    use SecurityQuestionValidationRules;

    private const VERIFICATION_TTL_MINUTES = 10;

    public function __construct(private readonly ResetUserPassword $resetUserPassword)
    {
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'security_question' => $this->securityQuestionRules(),
            'security_answer' => $this->securityAnswerRules(),
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user
            || $user->security_question !== $validated['security_question']
            || ! $user->verifySecurityAnswer($validated['security_answer'])) {
            return response()->json(['message' => "We couldn't verify your identity with that information."], 422);
        }

        $token = Str::random(48);
        Cache::put("password-reset-token:{$token}", $user->id, now()->addMinutes(self::VERIFICATION_TTL_MINUTES));

        return response()->json(['reset_token' => $token]);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'string'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $userId = Cache::get("password-reset-token:{$validated['reset_token']}");

        if (! $userId) {
            return response()->json(['message' => 'This reset link has expired. Please verify your identity again.'], 422);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['message' => 'This reset link has expired. Please verify your identity again.'], 422);
        }

        $this->resetUserPassword->reset($user, $validated);

        Cache::forget("password-reset-token:{$validated['reset_token']}");

        return response()->json(['message' => 'Your password has been reset.']);
    }
}
