<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use Illuminate\View\View;

class StaffProfileController extends Controller
{
    public function show(string $staffPublicId): View
    {
        $staff = User::query()
            ->where('staff_public_id', $staffPublicId)
            ->firstOrFail();

        $works = collect();
        $characters = collect();

        if ($staff->contributor_application_id) {
            $works = Work::query()
                ->where('contributor_application_id', $staff->contributor_application_id)
                ->where('status', 'published')
                ->latest()
                ->get();

            $characters = Character::query()
                ->with('work')
                ->where('contributor_application_id', $staff->contributor_application_id)
                ->where('status', 'published')
                ->latest()
                ->get();
        }

        return view('public.staff.show', [
            'staff' => $staff,
            'works' => $works,
            'characters' => $characters,
        ]);
    }
}
