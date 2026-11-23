<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->tenant_id) {
                session(['active_tenant_id' => $user->tenant_id]);
                session(['active_tenant_name' => $user->tenant?->name]);
            }
            if ($user->branch_id) {
                session(['active_branch_id' => $user->branch_id]);
                session(['active_branch_name' => $user->branch?->name]);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request)
    {
        $email = $request->input('email');
        $user = User::withoutGlobalScopes()->where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();

            if ($user->tenant_id) {
                session(['active_tenant_id' => $user->tenant_id]);
                session(['active_tenant_name' => $user->tenant?->name]);
            }
            if ($user->branch_id) {
                session(['active_branch_id' => $user->branch_id]);
                session(['active_branch_name' => $user->branch?->name]);
            } else {
                $mainBranch = Branch::withoutGlobalScopes()->where('tenant_id', $user->tenant_id)->where('is_main_branch', true)->first();
                if ($mainBranch) {
                    session(['active_branch_id' => $mainBranch->id]);
                    session(['active_branch_name' => $mainBranch->name]);
                }
            }

            return redirect()->route('dashboard')->with('success', "Logged in as {$user->name} ({$user->role_slug})");
        }

        return redirect()->route('login')->with('error', 'Demo user not found.');
    }

    public function switchBranch(Request $request, Branch $branch)
    {
        $user = Auth::user();

        if (!$user->canAccessBranch($branch->id)) {
            abort(403, 'Unauthorized access to this branch.');
        }

        session(['active_branch_id' => $branch->id]);
        session(['active_branch_name' => $branch->name]);

        return redirect()->back()->with('success', "Switched operating branch to: {$branch->name}");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been signed out.');
    }
}
