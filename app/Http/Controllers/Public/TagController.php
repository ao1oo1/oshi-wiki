<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::query()
            ->where('status', 'published')
            ->withCount([
                'works' => function ($query) {
                    $query->where('status', 'published');
                },
                'characters' => function ($query) {
                    $query->where('status', 'published');
                },
            ])
            ->orderBy('name')
            ->paginate(60)
            ->withQueryString();

        return view('public.tags.index', [
            'tags' => $tags,
        ]);
    }
}
