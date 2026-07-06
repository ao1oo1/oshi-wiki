<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\HelpfulVote;
use App\Models\Work;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HelpfulVoteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target_type' => ['required', 'in:work,character'],
            'target_id' => ['required', 'integer'],
        ]);

        $model = $data['target_type'] === 'work'
            ? Work::query()->where('status', 'published')->findOrFail($data['target_id'])
            : Character::query()->where('status', 'published')->findOrFail($data['target_id']);

        $sessionId = $request->session()->getId();

        $alreadyVoted = HelpfulVote::query()
            ->where('target_type', $data['target_type'])
            ->where('target_id', $model->id)
            ->where('session_id', $sessionId)
            ->exists();

        if (! $alreadyVoted) {
            HelpfulVote::create([
                'target_type' => $data['target_type'],
                'target_id' => $model->id,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
            ]);

            $model->increment('helpful_count');
        }

        return back()->with('success', '投票ありがとうございました。');
    }
}
