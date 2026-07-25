<?php

namespace App\Http\Controllers\Api;

use App\Actions\Teams\CreateTeam;
use App\Concerns\SecurityQuestionValidationRules;
use App\Http\Controllers\Controller;
use App\Support\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * JSON mirror of OnboardingController — only reachable for a role='member'
 * account (created via web social login with no role chosen yet). Desktop
 * registration always sets a role directly, so this only matters for a
 * social-login account subsequently logging into the desktop client.
 */
class OnboardingController extends Controller
{
    use SecurityQuestionValidationRules;

    public function __construct(private readonly CreateTeam $createTeam)
    {
    }

    public function update(Request $request)
    {
        $user = $request->user();

        abort_unless($user->role === 'member', 422, 'Your account has already completed onboarding.');

        $validated = Validator::make($request->all(), [
            'role' => ['required', 'string', 'in:student,lecturer'],
            'rules_agreement' => ['required', 'accepted'],
            'security_question' => $this->securityQuestionRules(),
            'security_answer' => $this->securityAnswerRules(),
        ])->validate();

        $user->forceFill([
            'role' => $validated['role'],
            'rules_agreed_at' => now(),
            'security_question' => $validated['security_question'],
            'security_answer' => SecurityQuestion::normalizeAnswer($validated['security_answer']),
        ])->save();

        if (! $user->currentTeam) {
            $this->createTeam->handle($user, $user->name."'s Team", isPersonal: true);
        }

        return response()->json($user->fresh());
    }

    public function decline(Request $request)
    {
        $user = $request->user();

        abort_unless($user->role === 'member', 422, 'Your account has already completed onboarding.');

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Your registration was cancelled because you did not agree to the platform rules.']);
    }
}
