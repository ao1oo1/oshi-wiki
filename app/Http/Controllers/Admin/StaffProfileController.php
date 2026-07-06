<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        $this->ensureStaffPublicId($user);

        return view('admin.staff_profile.edit', [
            'user' => $user->fresh(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $this->ensureStaffPublicId($user);

        $validated = $request->validate([
            'public_username' => ['required', 'string', 'max:50'],
            'profile_comment' => ['nullable', 'string', 'max:500'],
            'profile_icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ], [
            'public_username.required' => 'ユーザーネームを入力してください。',
            'public_username.max' => 'ユーザーネームは50文字以内で入力してください。',
            'profile_comment.max' => '一言文章は500文字以内で入力してください。',
            'profile_icon.image' => 'プロフィールアイコンは画像ファイルを指定してください。',
            'profile_icon.max' => 'プロフィールアイコンは2MB以内にしてください。',
        ]);

        if ($request->hasFile('profile_icon')) {
            if ($user->profile_icon_path) {
                Storage::disk('public')->delete($user->profile_icon_path);
            }

            $validated['profile_icon_path'] = $request->file('profile_icon')->store('staff-icons', 'public');
        }

        unset($validated['profile_icon']);

        $user->update($validated);

        return redirect()
            ->route('admin.staff-profile.edit')
            ->with('status', 'プロフィールを更新しました。');
    }

    private function ensureStaffPublicId($user): void
    {
        if (! empty($user->staff_public_id)) {
            return;
        }

        $user->forceFill([
            'staff_public_id' => 'STAFF-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'public_username' => $user->public_username ?: $user->name,
        ])->save();
    }
}
