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
            return redirect()->to($this->homeRouteFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(array_merge($credentials, ['is_active' => true]), $request->has('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            $this->syncSessionContext($user);

            return redirect()->intended($this->homeRouteFor($user));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records or your account is disabled.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request)
    {
        $email = $request->input('email');
        $user = User::withoutGlobalScopes()->where('email', $email)->where('is_active', true)->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            $this->syncSessionContext($user);

            return redirect()->to($this->homeRouteFor($user))
                ->with('success', "Logged in as {$user->name} (" . ucwords(str_replace('_', ' ', $user->primaryRoleName())) . ")");
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

    protected function syncSessionContext(User $user): void
    {
        session()->forget(['active_tenant_id', 'active_tenant_name', 'active_branch_id', 'active_branch_name']);

        if ($user->tenant_id) {
            session(['active_tenant_id' => $user->tenant_id]);
            session(['active_tenant_name' => $user->tenant?->name]);
        }

        if ($user->branch_id) {
            session(['active_branch_id' => $user->branch_id]);
            session(['active_branch_name' => $user->branch?->name]);
        } elseif ($user->tenant_id) {
            $mainBranch = Branch::withoutGlobalScopes()
                ->where('tenant_id', $user->tenant_id)
                ->where('is_main_branch', true)
                ->first();

            if ($mainBranch) {
                session(['active_branch_id' => $mainBranch->id]);
                session(['active_branch_name' => $mainBranch->name]);
            }
        }
    }

    protected function homeRouteFor(User $user): string
    {
        if (!$user->can('dashboard.view') && $user->can('student.portal')) {
            return route('student.portal');
        }

        return route('dashboard');
    }
}
