<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function show(Character $character): View
    {
        abort_unless($character->status === 'published', 404);
        abort_unless($character->work?->status === 'published', 404);

        $character->load([
            'work',
            'tags',
            'outgoingRelationships' => function ($query) {
                $query->where('status', 'published')
                    ->with('toCharacter');
            },
            'incomingRelationships' => function ($query) {
                $query->where('status', 'published')
                    ->with('fromCharacter');
            },
        ]);

        return view('public.characters.show', [
            'character' => $character,
        ]);
    }
}
