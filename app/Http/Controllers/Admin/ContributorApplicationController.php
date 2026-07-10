<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContributorApplication;
use App\Models\Role;
use App\Models\User;
use App\Notifications\StaffOnboardingNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function activate(ContributorApplication $contributorApplication): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $existingUser = User::query()
            ->where('email', $contributorApplication->email)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($contributorApplication) {
                $query->whereNull('contributor_application_id')
                    ->orWhere('contributor_application_id', '!=', $contributorApplication->id);
            })
            ->first();

        if ($existingUser) {
            return back()->withErrors([
                'email' => 'このメールアドレスはすでに最高管理者・管理スタッフ・一般執筆ユーザーのいずれかで使用されています。別のメールアドレスで申請し直してください。',
            ]);
        }

        $temporaryPassword = $this->generateTemporaryPassword();
        $user = null;

        DB::transaction(function () use ($contributorApplication, $temporaryPassword, &$user): void {
            $staffRole = Role::query()->firstOrCreate(
                ['name' => User::ROLE_STAFF],
                [
                    'label' => '管理スタッフ',
                    'description' => 'Oshi-Wikiの情報入力を行う管理スタッフ',
                ]
            );

            $user = User::query()->firstOrNew([
                'email' => $contributorApplication->email,
            ]);

            $user->forceFill([
                'name' => $contributorApplication->username,
                'public_username' => $contributorApplication->username,
                'role_id' => $staffRole->id,
                'status' => 'active',
                'is_super_admin' => false,
                'contributor_application_id' => $contributorApplication->id,
                'must_change_password' => true,
                'email_verified_at' => $user->email_verified_at ?: now(),
                'password' => Hash::make($temporaryPassword),
            ])->save();

            if (empty($user->staff_public_id)) {
                $user->forceFill([
                    'staff_public_id' => 'STAFF-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                ])->save();
            }

            $contributorApplication->update([
                'status' => 'active',
                'started_at' => $contributorApplication->started_at ?: now(),
            ]);
        });

        $user->notify(new StaffOnboardingNotification(
            loginUrl: route('admin.login'),
            email: $user->email,
            temporaryPassword: $temporaryPassword
        ));

        return back()->with('success', '登用開始にしました。スタッフ宛に仮パスワードと管理スタッフ用ログインURLを送信しました。');
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

        DB::transaction(function () use ($contributorApplication): void {
            User::query()
                ->where('contributor_application_id', $contributorApplication->id)
                ->whereNull('deleted_at')
                ->get()
                ->each(function (User $user): void {
                    $user->forceFill([
                        'status' => 'inactive',
                        'email' => 'deleted-' . now()->format('YmdHis') . '-' . $user->email,
                        'deleted_at' => now(),
                    ])->save();
                });

            $contributorApplication->delete();
        });

        return back()->with('success', '申請に削除フラグを付け、紐づくスタッフユーザーも停止しました。');
    }

    private function generateTemporaryPassword(): string
    {
        return 'Oshi-' . Str::random(12) . '-2026';
    }
}
