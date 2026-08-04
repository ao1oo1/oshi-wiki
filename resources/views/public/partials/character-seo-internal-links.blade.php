@if (
    ($seoCharacterWorks ?? collect())->isNotEmpty()
    || ($seoRelatedCharacters ?? collect())->isNotEmpty()
)
    <section
        class="oshi-card"
        aria-labelledby="character-internal-links-heading"
    >
        <h2
            id="character-internal-links-heading"
            class="mb-4 text-2xl font-bold"
        >
            関連ページ
        </h2>

        @if (($seoCharacterWorks ?? collect())->isNotEmpty())
            <div>
                <h3 class="font-bold">所属作品・登場する章</h3>

                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($seoCharacterWorks as $relatedWork)
                        <a
                            href="{{ route('public.works.show', $relatedWork) }}"
                            class="oshi-chip hover:underline"
                        >
                            {{ $relatedWork->title }}
                        </a>

                        <a
                            href="{{ route('public.works.show', $relatedWork) }}#work-story-sections"
                            class="oshi-chip hover:underline"
                        >
                            {{ $relatedWork->title }}の章・ストーリー
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if (($seoRelatedCharacters ?? collect())->isNotEmpty())
            <div class="mt-6">
                <h3 class="font-bold">同じ作品の関連キャラクター</h3>

                <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($seoRelatedCharacters as $relatedCharacter)
                        <a
                            href="{{ route('public.characters.show', $relatedCharacter) }}"
                            class="rounded-xl border border-[#E2E8F0] bg-white p-3 font-bold hover:border-[#FED7E2] hover:bg-[#FFF7FA]"
                        >
                            {{ $relatedCharacter->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endif
