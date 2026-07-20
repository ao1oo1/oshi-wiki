<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">作品商品リンク編集</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-[#718096]">
                        {{ $work->title }}
                    </p>
                    <h1 class="text-2xl font-bold text-[#2D3748]">
                        商品リンクを編集
                    </h1>
                </div>
                <a
                    href="{{ route('admin.works.monetization-links.index', $work) }}"
                    class="oshi-btn oshi-btn-sub"
                >
                    一覧へ戻る
                </a>
            </div>

            <form
                method="POST"
                action="{{ route('admin.works.monetization-links.update', [$work, $link]) }}"
            >
                @method('PUT')
                @include('admin.monetization.work-links._form')

                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="oshi-btn">更新する</button>
                    <a
                        href="{{ route('admin.works.monetization-links.index', $work) }}"
                        class="oshi-btn oshi-btn-sub"
                    >
                        キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
