@if (($seoRelatedWorks ?? collect())->isNotEmpty())
    <section
        class="oshi-card public-work-anchor-section"
        aria-labelledby="related-tag-works-heading"
    >
        <h2
            id="related-tag-works-heading"
            class="mb-4 text-2xl font-bold"
        >
            同じタグの作品
        </h2>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($seoRelatedWorks as $relatedWork)
                <a
                    href="{{ route('public.works.show', $relatedWork) }}"
                    class="rounded-2xl border border-[#E2E8F0] bg-white p-4 transition hover:border-[#FED7E2] hover:bg-[#FFF7FA]"
                >
                    <span class="font-bold text-[#2D3748]">
                        {{ $relatedWork->title }}
                    </span>

                    @if ($relatedWork->original_media)
                        <span class="mt-2 block text-xs text-[#718096]">
                            {{ $relatedWork->original_media }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
