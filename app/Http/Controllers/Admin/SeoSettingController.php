<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Support\SeoSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeoSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $data = $request->validate([
            'site_title' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'site_keywords' => ['nullable', 'string', 'max:2000'],
            'google_site_verification' => [
                'nullable',
                'string',
                'max:255',
            ],
            'default_og_image_url' => [
                'nullable',
                'url',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use ($data): void {
            $setting = SeoSetting::query()
                ->orderBy('id')
                ->first();

            if (! $setting) {
                $setting = new SeoSetting();
            }

            $setting->fill($data);
            $setting->save();

            SeoSetting::query()
                ->whereKeyNot($setting->getKey())
                ->delete();
        });

        SeoSettings::forget();

        return redirect()
            ->route('admin.analytics.index', ['tab' => 'seo'])
            ->with('success', 'SEO設定を保存しました。');
    }
}
