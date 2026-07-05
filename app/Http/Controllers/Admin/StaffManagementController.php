<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\ContributorApplication;
use App\Models\Work;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffManagementController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $staff = ContributorApplication::query()
            ->withTrashed()
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.staff.index', [
            'staff' => $staff,
        ]);
    }

    public function registered(ContributorApplication $staff): View
    {
        $this->authorizeSuperAdmin();

        return view('admin.staff.registered', [
            'staff' => $staff,
            'works' => Work::query()
                ->where('contributor_application_id', $staff->id)
                ->latest()
                ->get(),
            'characters' => Character::query()
                ->with('work')
                ->where('contributor_application_id', $staff->id)
                ->latest()
                ->get(),
        ]);
    }

    public function updateNotes(Request $request, ContributorApplication $staff): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $staff->update([
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return back()->with('success', '備考を保存しました。');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:activate,pause,delete'],
        ]);

        $staff = ContributorApplication::query()
            ->withTrashed()
            ->whereIn('id', $data['ids'])
            ->get();

        foreach ($staff as $item) {
            if ($data['action'] === 'activate') {
                if ($item->trashed()) {
                    $item->restore();
                }

                $item->update([
                    'status' => 'active',
                    'started_at' => $item->started_at ?: now(),
                    'paused_at' => null,
                ]);
            }

            if ($data['action'] === 'pause') {
                if (! $item->trashed()) {
                    $item->update([
                        'status' => 'paused',
                        'paused_at' => now(),
                    ]);
                }
            }

            if ($data['action'] === 'delete') {
                if (! $item->trashed()) {
                    $item->delete();
                }
            }
        }

        return back()->with('success', '一括操作を実行しました。');
    }
}
