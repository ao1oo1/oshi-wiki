<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\Tag;
use App\Models\Work;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewRequestController extends Controller
{
    private array $modelMap = [
        'works' => Work::class,
        'characters' => Character::class,
        'relationships' => CharacterRelationship::class,
        'tags' => Tag::class,
    ];

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        return view('admin.review_requests.index', [
            'works' => Work::query()->where('review_status', 'pending')->latest()->get(),
            'characters' => Character::query()->with('work')->where('review_status', 'pending')->latest()->get(),
            'relationships' => CharacterRelationship::query()
                ->with(['work', 'fromCharacter', 'toCharacter'])
                ->where('review_status', 'pending')
                ->latest()
                ->get(),
            'tags' => Tag::query()->where('review_status', 'pending')->latest()->get(),
        ]);
    }

    public function approve(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $model = $this->findTarget($request);

        $model->forceFill([
            'status' => 'published',
            'review_status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ])->save();

        return back()->with('success', '公開承認しました。');
    }

    public function reject(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $model = $this->findTarget($request);

        $model->forceFill([
            'status' => 'private',
            'review_status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ])->save();

        return back()->with('success', '非公開として差し戻しました。');
    }

    private function findTarget(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:works,characters,relationships,tags'],
            'id' => ['required', 'integer'],
        ]);

        $class = $this->modelMap[$data['type']];

        return $class::query()->findOrFail($data['id']);
    }
}
