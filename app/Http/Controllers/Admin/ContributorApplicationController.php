<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContributorApplication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContributorApplicationController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $applications = ContributorApplication::query()
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.contributor_applications.index', [
            'applications' => $applications,
        ]);
    }

    public function activate(Request $request, ContributorApplication $contributorApplication): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'temporary_password' => ['nullable', 'string', 'min:8', 'max:100'],
        ], [], [
            'temporary_password' => '一時パスワード',
        ]);

        $temporaryPassword = $data['temporary_password'] ?: Str::password(12);

        $contributorApplication->update([
            'status' => 'active',
            'started_at' => $contributorApplication->started_at ?: now(),
        ]);

        User::updateOrCreate(
            ['email' => $contributorApplication->email],
            [
                'name' => $contributorApplication->username,
                'password' => Hash::make($temporaryPassword),
                'status' => 'active',
                'must_change_password' => true,
                'contributor_application_id' => $contributorApplication->id,
                'email_verified_at' => now(),
            ]
        );

        Mail::raw(
            <<<TEXT
{$contributorApplication->username} 様

Oshi-Wiki 情報入力スタッフとして登用されました。

以下の情報でログインしてください。

ログインURL：
https://oshi-wiki.com/login

メールアドレス：
{$contributorApplication->email}

初回パスワード：
{$temporaryPassword}

初回ログイン後、ご自身で新しいパスワードを設定してください。
このパスワードは他の方に共有しないでください。

Oshi-Wiki
TEXT,
            function ($message) use ($contributorApplication) {
                $message
                    ->to($contributorApplication->email)
                    ->subject('【Oshi-Wiki】情報入力スタッフ登用のお知らせ');
            }
        );

        return back()->with('success', '登用開始にしました。初回パスワード案内メールを送信しました。');
    }

    public function reject(ContributorApplication $contributorApplication): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $contributorApplication->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', '見送りにしました。');
    }

    public function destroy(ContributorApplication $contributorApplication): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $contributorApplication->delete();

        return back()->with('success', '申請に削除フラグを付けました。');
    }
}
