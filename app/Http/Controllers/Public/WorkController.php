<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Work;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function home(): View
    {
        $keyword = trim((string) request('keyword', ''));
        $tagId = request('tag_id');

        if ($keyword !== '' || $tagId) {
            return $this->index();
        }

        $works = Work::query()
            ->with(['tags', 'characters.tags'])
            ->where('status', 'published')
            ->latest()
            ->limit(9)
            ->get();

        $worksCount = Work::query()
            ->where('status', 'published')
            ->count();

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
            ->limit(18)
            ->get();

        $tagsCount = Tag::query()
            ->where('status', 'published')
            ->count();

        return view('public.works.index', [
            'works' => $works,
            'tags' => $tags,
            'keyword' => $keyword,
            'selectedTagId' => $tagId,
            'isHome' => true,
            'worksCount' => $worksCount,
            'tagsCount' => $tagsCount,
        ]);
    }

    public function index(): View
    {
        $keyword = trim((string) request('keyword', ''));
        $tagId = request('tag_id');

        $keywords = collect(preg_split('/[\s　]+/u', $keyword))
            ->filter()
            ->values();

        $works = Work::query()
            ->with(['tags', 'characters.tags'])
            ->where('status', 'published')
            ->when($tagId, function ($query) use ($tagId) {
                $query->whereHas('tags', function ($query) use ($tagId) {
                    $query->where('tags.id', $tagId);
                });
            })
            ->when($keywords->count(), function ($query) use ($keywords) {
                foreach ($keywords as $word) {
                    $query->where(function ($query) use ($word) {
                        $like = '%' . $word . '%';

                        $query->where('title', 'like', $like)
                            ->orWhere('title_kana', 'like', $like)
                            ->orWhere('genre', 'like', $like)
                            ->orWhere('original_media', 'like', $like)
                            ->orWhere('description', 'like', $like)
                            ->orWhereHas('tags', function ($query) use ($like) {
                                $query->where('tags.name', 'like', $like)
                                    ->orWhere('tags.type', 'like', $like)
                                    ->orWhere('tags.description', 'like', $like);
                            })
                            ->orWhereHas('characters', function ($query) use ($like) {
                                $query->where('characters.status', 'published')
                                    ->where(function ($query) use ($like) {
                                        $query->where('characters.name', 'like', $like)
                                            ->orWhere('characters.name_kana', 'like', $like)
                                            ->orWhere('characters.age', 'like', $like)
                                            ->orWhere('characters.affiliation', 'like', $like)
                                            ->orWhere('characters.grade_class', 'like', $like)
                                            ->orWhere('characters.first_person', 'like', $like)
                                            ->orWhere('characters.tone', 'like', $like)
                                            ->orWhere('characters.tone_examples', 'like', $like)
                                            ->orWhere('characters.personality', 'like', $like)
                                            ->orWhere('characters.appearance', 'like', $like)
                                            ->orWhere('characters.background', 'like', $like)
                                            ->orWhereHas('tags', function ($query) use ($like) {
                                                $query->where('tags.name', 'like', $like)
                                                    ->orWhere('tags.type', 'like', $like)
                                                    ->orWhere('tags.description', 'like', $like);
                                            });
                                    });
                            });
                    });
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

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
            ->get();

        return view('public.works.index', [
            'works' => $works,
            'tags' => $tags,
            'keyword' => $keyword,
            'selectedTagId' => $tagId,
            'isHome' => false,
            'worksCount' => null,
            'tagsCount' => null,
        ]);
    }

    public function show(Work $work): View
    {
        abort_unless($work->status === 'published', 404);

        $work->load([
            'tags',
            'characters' => function ($query) {
                $query->where('status', 'published')
                    ->with('tags')
                    ->latest();
            },
            'characterRelationships' => function ($query) {
                $query->where('status', 'published')
                    ->with(['fromCharacter', 'toCharacter'])
                    ->latest();
            },
        ]);

        return view('public.works.show', [
            'work' => $work,
        ]);
    }
}
