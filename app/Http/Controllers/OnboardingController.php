<?php

namespace App\Http\Controllers;

use App\Actions\Teams\CreateTeam;
use App\Concerns\SecurityQuestionValidationRules;
use App\Support\SecurityQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    use SecurityQuestionValidationRules;

    public function __construct(private readonly CreateTeam $createTeam)
    {
        //
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'member') {
            return redirect()->route($user->dashboardRouteName());
        }

        return view('pages::auth.onboarding', [
            'securityQuestions' => SecurityQuestion::OPTIONS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'member') {
            return redirect()->route($user->dashboardRouteName());
        }

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

        return redirect()->route($user->dashboardRouteName());
    }
}
