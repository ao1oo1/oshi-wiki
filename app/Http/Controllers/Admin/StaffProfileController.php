<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StaffProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.staff_profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'public_username' => ['required', 'string', 'max:255'],
            'profile_comment' => ['nullable', 'string', 'max:500'],
            'profile_icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ], [], [
            'public_username' => 'ユーザーネーム',
            'profile_comment' => '一言文章',
            'profile_icon' => 'プロフィールアイコン',
        ]);

        $user = $request->user();

        if ($request->hasFile('profile_icon')) {
            if ($user->profile_icon_path) {
                Storage::disk('public')->delete($user->profile_icon_path);
            }

            $data['profile_icon_path'] = $request->file('profile_icon')->store('staff-icons', 'public');
        }

        unset($data['profile_icon']);

        $user->forceFill($data)->save();

        return back()->with('success', 'プロフィールを保存しました。');
    }
}
