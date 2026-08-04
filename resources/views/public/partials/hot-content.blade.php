@if (($hotWorks ?? collect())->isNotEmpty() || ($hotCharacters ?? collect())->isNotEmpty())
<section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8" aria-labelledby="hot-content-heading">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-black tracking-[0.18em] text-[#E879A0]">HOT!</p>
            <h2 id="hot-content-heading" class="mt-1 text-2xl font-black text-[#2D3748] sm:text-3xl">
                今、よく見られているページ
            </h2>
        </div>
        <p class="text-xs text-[#718096] sm:text-sm">直近{{ $hotPeriodDays }}日間のアクセスを集計</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl border border-[#FED7E2] bg-[#FFF7FA] p-5 sm:p-6">
            <div class="mb-4 flex items-center gap-3">
                <span class="rounded-full bg-[#E879A0] px-3 py-1 text-xs font-black text-white">HOT!</span>
                <h3 class="text-xl font-black text-[#2D3748]">HOT作品</h3>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @forelse ($hotWorks as $index => $work)
                    <a href="{{ route('public.works.show',$work) }}" class="group rounded-2xl border border-white bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#F7A8BE] hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#FFF0F5] text-sm font-black text-[#D95F82]">{{ $index+1 }}</span>
                            <div class="min-w-0">
                                <h4 class="font-black leading-6 text-[#2D3748] group-hover:text-[#D95F82]">{{ $work->title }}</h4>
                                @if($work->original_media)<p class="mt-1 text-xs text-[#718096]">{{ $work->original_media }}</p>@endif
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-[#718096]">集計中です。</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-[#D9E8FF] bg-[#F5F9FF] p-5 sm:p-6">
            <div class="mb-4 flex items-center gap-3">
                <span class="rounded-full bg-[#5C8FD8] px-3 py-1 text-xs font-black text-white">HOT!</span>
                <h3 class="text-xl font-black text-[#2D3748]">HOTキャラクター</h3>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @forelse ($hotCharacters as $index => $character)
                    <a href="{{ route('public.characters.show',$character) }}" class="group rounded-2xl border border-white bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#9DBBE8] hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#EDF4FF] text-sm font-black text-[#477CC6]">{{ $index+1 }}</span>
                            <div class="min-w-0">
                                <h4 class="font-black leading-6 text-[#2D3748] group-hover:text-[#477CC6]">{{ $character->name }}</h4>
                                @if($character->affiliation || $character->occupation_position)
                                    <p class="mt-1 line-clamp-1 text-xs text-[#718096]">{{ $character->occupation_position ?: $character->affiliation }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-[#718096]">集計中です。</p>
                @endforelse
            </div>
        </section>
    </div>
</section>
@endif
