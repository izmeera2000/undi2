<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Models\User;



use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\ForgotPasswordMail;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)
            ->where('status', 'active')
            ->first();
            
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }




        $token = Password::createToken($user);

        // Generate reset URL
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        // Attach URL to user object (for Blade email)
        $user->reset_url = $resetUrl;

        // Send custom mail
        Mail::to($user->email)->send(new ForgotPasswordMail($user, $resetUrl));

        return back()->with('status', 'Reset link sent successfully.');
    }

}
