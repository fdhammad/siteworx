<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:8|max:14',
        ]);
    
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();
    
                event(new PasswordReset($user));
            }
        );
    
        if ($status == Password::PASSWORD_RESET) {
            $user = \App\Models\User::where('email', $request->email)->first();
    
            if ($user) {
                // Adjust based on your database field for role
                switch ($user->user_type ?? null) {
                    case 'user':
                        return redirect()->route('user.login')->with('status', __($status)); // landing-page/login
                    case 'admin':
                    case 'provider':
                    case 'handyman':
                        return redirect()->route('auth.login')->with('status', __($status)); // backend auth login
                    default:
                        return redirect()->route('login')->with('status', __($status));
                }
            }
    
            return redirect()->route('login')->with('status', __($status));
        }
    
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
    
}
