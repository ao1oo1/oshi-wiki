<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Character\BulkActionCharacterRequest;
use App\Models\Character;
use Illuminate\Http\RedirectResponse;

class CharacterBulkActionController extends Controller
{
    public function __invoke(BulkActionCharacterRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $ids = $request->input('character_ids', []);
        $action = $request->input('bulk_action');

        $characters = Character::query()
            ->whereIn('id', $ids)
            ->get();

        if ($characters->isEmpty()) {
            return back()->withErrors([
                'character_ids' => '対象のキャラクターが見つかりませんでした。',
            ]);
        }

        if ($action === 'publish') {
            Character::query()
                ->whereIn('id', $ids)
                ->update(['status' => 'published']);

            return back()->with('success', $characters->count() . '件のキャラクターを公開にしました。');
        }

        if ($action === 'private') {
            Character::query()
                ->whereIn('id', $ids)
                ->update(['status' => 'private']);

            return back()->with('success', $characters->count() . '件のキャラクターを非公開にしました。');
        }

        if ($action === 'delete') {
            foreach ($characters as $character) {
                $character->delete();
            }

            return back()->with('success', $characters->count() . '件のキャラクターに削除フラグを付けました。');
        }

        return back();
    }
}
