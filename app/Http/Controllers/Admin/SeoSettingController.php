<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeoSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $data = $request->validate([
            'site_title' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'site_keywords' => ['nullable', 'string', 'max:2000'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'default_og_image_url' => ['nullable', 'url', 'max:2000'],
        ]);

        SeoSetting::query()->updateOrCreate(['id' => 1], $data);

        return redirect()
            ->route('admin.analytics.index', ['tab' => 'seo'])
            ->with('success', 'SEO設定を保存しました。');
    }
}
