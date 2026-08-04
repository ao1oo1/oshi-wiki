<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\Work;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GeneratePublicSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description =
        '公開中の作品・キャラクターを含むサイトマップを生成';

    public function handle(): int
    {
        $urls = collect([
            [
                'loc' => url('/'),
                'lastmod' => now()->toAtomString(),
            ],
        ]);

        Work::query()
            ->where('status', 'published')
            ->orderBy('id')
            ->chunkById(500, function ($works) use ($urls): void {
                foreach ($works as $work) {
                    $urls->push([
                        'loc' => route(
                            'public.works.show',
                            $work
                        ),
                        'lastmod' => $work->updated_at
                            ?->toAtomString(),
                    ]);
                }
            });

        Character::query()
            ->where('status', 'published')
            ->where(function ($characterQuery): void {
                $characterQuery
                    ->whereHas(
                        'linkedWorks',
                        fn ($query) => $query
                            ->where(
                                'works.status',
                                'published'
                            )
                    )
                    ->orWhereHas(
                        'work',
                        fn ($query) => $query
                            ->where(
                                'status',
                                'published'
                            )
                    );
            })
            ->orderBy('id')
            ->chunkById(
                500,
                function ($characters) use ($urls): void {
                    foreach ($characters as $character) {
                        $urls->push([
                            'loc' => route(
                                'public.characters.show',
                                $character
                            ),
                            'lastmod' => $character->updated_at
                                ?->toAtomString(),
                        ]);
                    }
                }
            );

        $xml = view(
            'public.sitemap.generated',
            ['urls' => $urls]
        )->render();

        $directory = public_path();
        File::ensureDirectoryExists($directory);

        $temporary = public_path(
            'sitemap.xml.tmp'
        );

        File::put($temporary, $xml);
        File::move(
            $temporary,
            public_path('sitemap.xml')
        );

        $this->info(
            'sitemap generated: '
            . $urls->count()
            . ' URLs'
        );

        return self::SUCCESS;
    }
}
