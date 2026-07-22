<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\ResetUserPassword;
use App\Concerns\SecurityQuestionValidationRules;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SecurityQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SecurityQuestionPasswordController extends Controller
{
    use SecurityQuestionValidationRules;

    /**
     * How long a successful security-question verification remains usable.
     */
    private const VERIFICATION_TTL_MINUTES = 10;

    public function __construct(private readonly ResetUserPassword $resetUserPassword)
    {
        //
    }

    /**
     * Show the "forgot password" form with the security question picker.
     */
    public function create(): View
    {
        return view('pages::auth.forgot-password', [
            'securityQuestions' => SecurityQuestion::OPTIONS,
        ]);
    }

    /**
     * Verify the submitted email, chosen question, and answer.
     */
    public function verify(Request $request): RedirectResponse
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
            return back()->withInput($request->only('email', 'security_question'))->withErrors([
                'security_answer' => "We couldn't verify your identity with that information.",
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('security_reset.user_id', $user->id);
        $request->session()->put('security_reset.verified_at', now());

        return redirect()->route('password.reset');
    }

    /**
     * Show the "set a new password" form, once verified.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        if (! $this->verifiedUser($request)) {
            return redirect()->route('password.request')->withErrors([
                'security_answer' => 'Please verify your identity before setting a new password.',
            ]);
        }

        return view('pages::auth.reset-password');
    }

    /**
     * Set the new password for the verified user.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $this->verifiedUser($request);

        if (! $user) {
            return redirect()->route('password.request')->withErrors([
                'security_answer' => 'Please verify your identity before setting a new password.',
            ]);
        }

        $this->resetUserPassword->reset($user, $request->all());

        $request->session()->forget(['security_reset.user_id', 'security_reset.verified_at']);

        return redirect()->route('login')->with('status', 'Your password has been reset.');
    }

    /**
     * Resolve the user verified via security question in this session, if the
     * verification is still fresh.
     */
    private function verifiedUser(Request $request): ?User
    {
        $verifiedAt = $request->session()->get('security_reset.verified_at');
        $userId = $request->session()->get('security_reset.user_id');

        if (! $userId || ! $verifiedAt || now()->diffInMinutes($verifiedAt) > self::VERIFICATION_TTL_MINUTES) {
            return null;
        }

        return User::find($userId);
    }
}
