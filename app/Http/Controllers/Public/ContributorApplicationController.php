<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreContributorApplicationRequest;
use App\Models\ContributorApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContributorApplicationController extends Controller
{
    public function create(): View
    {
        return view('public.contributor.apply');
    }

    public function store(StoreContributorApplicationRequest $request): RedirectResponse
    {
        ContributorApplication::create([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'discord_id' => $request->input('discord_id'),
            'applied_at' => now(),
            'started_at' => null,
            'registered_works_count' => 0,
            'registered_characters_count' => 0,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('public.contributor.apply')
            ->with('success', '申請を受け付けました。確認後、必要に応じてご連絡いたします。');
    }
}
