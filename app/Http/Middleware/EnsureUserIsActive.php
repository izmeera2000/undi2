<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 🚫 Suspended users
        if ($user->status === 'suspended') {
            Auth::logout();


            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Your account has been suspended. Please contact support.'
                ]);
        }

        // 🔥 First-time login users
        if ($user->status === 'pending') {
            return redirect()->route('first.login');
        }

        return $next($request);
    }
}
