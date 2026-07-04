<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContributorApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContributorApplicationController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $applications = ContributorApplication::query()
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.contributor_applications.index', [
            'applications' => $applications,
        ]);
    }

    public function activate(ContributorApplication $contributorApplication): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $contributorApplication->update([
            'status' => 'active',
            'started_at' => $contributorApplication->started_at ?: now(),
        ]);

        return back()->with('success', '登用開始にしました。');
    }

    public function reject(ContributorApplication $contributorApplication): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $contributorApplication->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', '見送りにしました。');
    }

    public function destroy(ContributorApplication $contributorApplication): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $contributorApplication->delete();

        return back()->with('success', '申請に削除フラグを付けました。');
    }
}
