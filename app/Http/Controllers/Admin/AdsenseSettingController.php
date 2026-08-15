<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdsenseSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdsenseSettingController extends Controller
{
    public function edit(): View
    {
        $this->ensureSuperAdmin();

        return view('admin.monetization.adsense.edit', [
            'setting' => AdsenseSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'publisher_id' => [
                'required',
                'string',
                'max:64',
                'regex:/^ca-pub-[0-9]+$/',
            ],
            'is_enabled' => ['nullable', 'boolean'],
            'auto_ads_enabled' => ['nullable', 'boolean'],
        ], [
            'publisher_id.regex' => 'パブリッシャーIDは ca-pub- から始まる形式で入力してください。',
        ]);

        AdsenseSetting::current()->update([
            'publisher_id' => $validated['publisher_id'],
            'is_enabled' => $request->boolean('is_enabled'),
            'auto_ads_enabled' => $request->boolean('auto_ads_enabled'),
        ]);

        return redirect()
            ->route('admin.monetization.adsense.edit')
            ->with('success', 'Google AdSense設定を更新しました。');
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '収益管理は最高管理者のみ利用できます。'
        );
    }
}
