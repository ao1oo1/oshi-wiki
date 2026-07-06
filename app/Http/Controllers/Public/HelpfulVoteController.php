<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HelpfulVoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_type' => ['required', 'in:work,character'],
            'target_id' => ['required', 'integer'],
        ]);

        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();

        $exists = DB::table('helpful_votes')
            ->where('target_type', $validated['target_type'])
            ->where('target_id', $validated['target_id'])
            ->where(function ($query) use ($sessionId, $ipAddress) {
                $query->where('session_id', $sessionId)
                    ->orWhere('ip_address', $ipAddress);
            })
            ->exists();

        if ($exists) {
            return back()->with('status', 'すでに「役に立った」を押しています。');
        }

        $model = match ($validated['target_type']) {
            'work' => Work::query()->findOrFail($validated['target_id']),
            'character' => Character::query()->findOrFail($validated['target_id']),
        };

        DB::transaction(function () use ($validated, $sessionId, $ipAddress, $model) {
            DB::table('helpful_votes')->insert([
                'target_type' => $validated['target_type'],
                'target_id' => $validated['target_id'],
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $model->increment('helpful_count');
        });

        return back()->with('status', 'ありがとうございます。「役に立った」に追加しました。');
    }
}
