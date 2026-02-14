<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Log incoming request
        Log::info('Password reset request received', [
            'email' => $request->email,
            'token' => $request->token,
        ]);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                // Log which user is being updated
                Log::info('Resetting password for user', ['id' => $user->id, 'email' => $user->email]);

                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Log::info('Password reset successfully for user', ['id' => $user->id]);
            }
        );

        // Log the status returned by Password::reset
        Log::info('Password reset status', ['status' => $status]);

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
