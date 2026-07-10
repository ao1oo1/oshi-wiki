<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Models\OriginalCharacter;
use App\Models\OriginalCharacterRelationship;
use App\Models\SavedPrompt;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Support\WritingAssistLimits;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $originalCharacterQuery = OriginalCharacter::query()->forUser($user);
        $relationshipQuery = OriginalCharacterRelationship::query()->forUser($user);
        $promptQuery = SavedPrompt::query()->forUser($user);

        $promptCount = (clone $promptQuery)->count();
        $activePromptCount = (clone $promptQuery)->where('status', 'active')->count();
        $draftPromptCount = (clone $promptQuery)->where('status', 'draft')->count();
        $totalUsedCount = (clone $promptQuery)->sum('used_count');

        $recentPrompts = (clone $promptQuery)
            ->orderByDesc('last_used_at')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('writer.dashboard', [
            'originalCharacterCount' => (clone $originalCharacterQuery)->count(),
            'relationshipCount' => (clone $relationshipQuery)->count(),
            'promptCount' => $promptCount,
            'activePromptCount' => $activePromptCount,
            'draftPromptCount' => $draftPromptCount,
            'totalUsedCount' => $totalUsedCount,
            'recentPrompts' => $recentPrompts,
            'originalCharacterLimit' => WritingAssistLimits::originalCharactersPerUser($user),
            'relationshipLimit' => WritingAssistLimits::relationshipsPerUser($user),
            'promptLimit' => WritingAssistLimits::promptsPerUser($user),
        ]);
    }
}
