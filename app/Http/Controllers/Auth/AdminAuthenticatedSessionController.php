<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.admin-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が正しくありません。',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user || ! method_exists($user, 'canAccessAdmin') || ! $user->canAccessAdmin()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => '管理画面にアクセスできる権限がありません。',
            ]);
        }

        if ($user->must_change_password) {
            return redirect()
                ->route('profile.edit')
                ->with('status', '初回ログインのため、新しいパスワードを設定してください。');
        }

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }
}
