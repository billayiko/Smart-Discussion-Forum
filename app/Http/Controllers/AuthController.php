<?php

namespace App\Http\Controllers;

use App\Actions\Teams\CreateTeam;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Concerns\SecurityQuestionValidationRules;
use App\Models\User;
use App\Support\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use PasswordValidationRules, ProfileValidationRules, SecurityQuestionValidationRules;

    public function __construct(private readonly CreateTeam $createTeam)
    {
    }

    /**
     * Mirrors App\Actions\Fortify\CreateNewUser exactly (same validation,
     * same fields, same personal-team creation) so a desktop registrant
     * ends up in the identical state as a real web signup — a functional
     * student/lecturer account, not stuck at the default 'member' role.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            ...$this->profileRules(),
            'role' => ['required', 'string', 'in:student,lecturer'],
            'password' => $this->passwordRules(),
            'rules_agreement' => ['required', 'accepted'],
            'security_question' => $this->securityQuestionRules(),
            'security_answer' => $this->securityAnswerRules(),
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
                'rules_agreed_at' => now(),
                'security_question' => $validated['security_question'],
                'security_answer' => SecurityQuestion::normalizeAnswer($validated['security_answer']),
            ]);

            $this->createTeam->handle($user, $user->name."'s Team", isPersonal: true);

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    // 2. LOGIN ENDPOINT
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    // 3. LOGOUT ENDPOINT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    // 4. CURRENT USER ENDPOINT
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
