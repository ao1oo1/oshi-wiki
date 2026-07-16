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
            ->with([
                'tags',
                'linkedCharacters' => function ($query): void {
                    $query
                        ->where('characters.status', 'published')
                        ->with('tags')
                        ->orderBy('characters.name');
                },
            ])
            ->where('status', 'published')
            ->whereNull('parent_work_id')
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

        $works->each(function (Work $work): void {
            $work->setRelation(
                'characters',
                $work->linkedCharacters
            );
        });

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
            ->with([
                'tags',
                'linkedCharacters' => function ($query): void {
                    $query
                        ->where('characters.status', 'published')
                        ->with('tags')
                        ->orderBy('characters.name');
                },
            ])
            ->where('status', 'published')
            ->whereNull('parent_work_id')
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
                            ->orWhereHas('linkedCharacters', function ($query) use ($like) {
                                $query->where('characters.status', 'published')
                                    ->where(function ($query) use ($like) {
                                        $query->where('characters.name', 'like', $like)
                                            ->orWhere('characters.name_kana', 'like', $like)
                                            ->orWhere('characters.real_name', 'like', $like)
                                            ->orWhere('characters.aliases', 'like', $like)
                                            ->orWhere('characters.name_english', 'like', $like)
                                            ->orWhere('characters.gender', 'like', $like)
                                            ->orWhere('characters.age', 'like', $like)
                                            ->orWhere('characters.birthday', 'like', $like)
                                            ->orWhere('characters.height', 'like', $like)
                                            ->orWhere('characters.weight', 'like', $like)
                                            ->orWhere('characters.blood_type', 'like', $like)
                                            ->orWhere('characters.birthplace', 'like', $like)
                                            ->orWhere('characters.species', 'like', $like)
                                            ->orWhere('characters.affiliation', 'like', $like)
                                            ->orWhere('characters.school_grade_class', 'like', $like)
                                            ->orWhere('characters.occupation_position', 'like', $like)
                                            ->orWhere('characters.family_structure', 'like', $like)
                                            ->orWhere('characters.first_person', 'like', $like)
                                            ->orWhere('characters.second_person', 'like', $like)
                                            ->orWhere('characters.basic_tone', 'like', $like)
                                            ->orWhere('characters.catchphrases', 'like', $like)
                                            ->orWhere('characters.distinctive_speech', 'like', $like)
                                            ->orWhere('characters.tone_by_relationship', 'like', $like)
                                            ->orWhere('characters.short_quote_examples', 'like', $like)
                                            ->orWhere('characters.personality', 'like', $like)
                                            ->orWhere('characters.appearance', 'like', $like)
                                            ->orWhere('characters.abilities', 'like', $like)
                                            ->orWhere('characters.background', 'like', $like)
                                            ->orWhere('characters.story_activities', 'like', $like)
                                            ->orWhere('characters.source_title', 'like', $like)
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

        $works->each(function (Work $work): void {
            $work->setRelation(
                'characters',
                $work->linkedCharacters
            );
        });

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
        $work->loadMissing([
            'parentWork',
            'publishedChildWorks',
        ]);

        if (
            $work->parentWork
            && ! $work->parentWork->isPublished()
        ) {
            abort(404);
        }


        abort_unless($work->status === 'published', 404);

        $work->load([
            'tags',
            'linkedCharacters' => function ($query) {
                $query->where('characters.status', 'published')
                    ->with('tags')
                    ->orderBy('characters.name');
            },
            'characterRelationships' => function ($query) {
                $query->where('status', 'published')
                    ->with(['fromCharacter', 'toCharacter'])
                    ->latest();
            },
        ]);

        $work->setRelation(
            'characters',
            $work->linkedCharacters
        );

        return view('public.works.show', [
            'work' => $work,
        ]);
    }
}
