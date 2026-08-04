<?php

namespace App\Providers;

use App\Models\Character;
use App\Models\Work;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PublicSeoLinkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer(
            'public.works.show',
            function ($view): void {
                $work = $view->getData()['work'] ?? null;

                if (! $work instanceof Work) {
                    return;
                }

                $work->loadMissing('tags');

                $tagIds = $work->tags->pluck('id');

                $related = Work::query()
                    ->where('status', 'published')
                    ->whereKeyNot($work->getKey())
                    ->when(
                        $tagIds->isNotEmpty(),
                        fn ($query) => $query->whereHas(
                            'tags',
                            fn ($tagQuery) => $tagQuery
                                ->whereIn('tags.id', $tagIds)
                        ),
                        fn ($query) => $query->whereRaw('1 = 0')
                    )
                    ->orderByDesc('updated_at')
                    ->limit(6)
                    ->get();

                $view->with(
                    'seoRelatedWorks',
                    $related
                );
            }
        );

        View::composer(
            'public.characters.show',
            function ($view): void {
                $character = $view->getData()['character'] ?? null;

                if (! $character instanceof Character) {
                    return;
                }

                $character->loadMissing(['linkedWorks', 'work']);

                $works = $character->linkedWorks
                    ->filter(
                        fn ($work) => $work->status === 'published'
                    )
                    ->when(
                        $character->work?->status === 'published',
                        function ($works) use ($character) {
                            if (
                                ! $works->contains(
                                    fn ($work) =>
                                        $work->is($character->work)
                                )
                            ) {
                                $works->prepend($character->work);
                            }

                            return $works;
                        }
                    )
                    ->unique('id')
                    ->values();

                $workIds = $works->pluck('id');

                $related = Character::query()
                    ->where('status', 'published')
                    ->whereKeyNot($character->getKey())
                    ->when(
                        $workIds->isNotEmpty(),
                        fn ($query) => $query
                            ->where(function ($characterQuery) use (
                                $workIds
                            ): void {
                                $characterQuery
                                    ->whereHas(
                                        'linkedWorks',
                                        fn ($workQuery) => $workQuery
                                            ->whereIn(
                                                'works.id',
                                                $workIds
                                            )
                                            ->where(
                                                'works.status',
                                                'published'
                                            )
                                    )
                                    ->orWhereIn(
                                        'work_id',
                                        $workIds
                                    );
                            }),
                        fn ($query) => $query->whereRaw('1 = 0')
                    )
                    ->orderBy('id')
                    ->limit(6)
                    ->get();

                $view->with([
                    'seoCharacterWorks' => $works,
                    'seoRelatedCharacters' => $related,
                ]);
            }
        );
    }
}
