<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Work;
use App\Models\Character;

class StaffProfileController extends Controller
{
    public function show(string $staffPublicId)
    {
        $user = User::query()
            ->where('staff_public_id', $staffPublicId)
            ->firstOrFail();

        $works = collect();
        $characters = collect();

        if (! empty($user->contributor_application_id)) {
            if (class_exists(Work::class)) {
                $works = Work::query()
                    ->where('contributor_application_id', $user->contributor_application_id)
                    ->latest()
                    ->take(12)
                    ->get();
            }

            if (class_exists(Character::class)) {
                $characters = Character::query()
                    ->where('contributor_application_id', $user->contributor_application_id)
                    ->latest()
                    ->take(12)
                    ->get();
            }
        }

        return view('public.staff.show', [
            'staff' => $user,
            'works' => $works,
            'characters' => $characters,
        ]);
    }
}
