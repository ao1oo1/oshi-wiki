<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\Work;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'workCount' => Work::query()->count(),
            'characterCount' => Character::query()->count(),
            'relationshipCount' => CharacterRelationship::query()->count(),
            'latestWorks' => Work::query()
                ->latest()
                ->limit(5)
                ->get(),
            'latestCharacters' => Character::query()
                ->with('work')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
