<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">テキスト取り込み</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">テキスト取り込み</h1>
                    <p class="oshi-muted">作品設定テキストを貼り付けて、作品情報を自動登録します。</p>
                </div>

                <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">作品一覧へ戻る</a>
            </div>

            <form method="POST" action="{{ route('admin.works.import.store') }}">
                @csrf

                <div class="mb-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="parent_work_id" class="mb-1 block font-medium">
                            親作品
                        </label>
                        <select
                            id="parent_work_id"
                            name="parent_work_id"
                            class="w-full"
                        >
                            <option value="">親作品なし（親・単独作品）</option>
                            @foreach (($parentWorkOptions ?? collect()) as $parentOption)
                                <option
                                    value="{{ $parentOption->id }}"
                                    @selected(
                                        (int) old('parent_work_id')
                                        === (int) $parentOption->id
                                    )
                                >
                                    {{ $parentOption->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="child_sort_order" class="mb-1 block font-medium">
                            関連作品の表示順
                        </label>
                        <input
                            id="child_sort_order"
                            type="number"
                            min="0"
                            max="9999"
                            name="child_sort_order"
                            value="{{ old('child_sort_order', 0) }}"
                            class="w-full"
                        >
                        <p class="mt-1 text-sm text-gray-600">
                            数字が小さい作品から順に表示します。
                        </p>
                    </div>
                </div>

                <div class="mb-5">
                    <label for="status" class="mb-1 block font-medium">状態</label>
                    <select id="status" name="status" class="w-full">
                        <option value="draft" @selected(old('status', 'draft') === 'draft')>下書き</option>
                        <option value="published" @selected(old('status') === 'published')>公開</option>
                        <option value="private" @selected(old('status') === 'private')>非公開</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label for="raw_text" class="mb-1 block font-medium">作品設定テキスト</label>
                    <textarea id="raw_text" name="raw_text" rows="16" class="w-full">{{ old('raw_text') }}</textarea>
                </div>

                @if (session('parsed'))
                    <div class="mb-5 rounded bg-pink-50 p-4">
                        <h2 class="mb-2 font-bold">読み取り結果</h2>
                        <pre class="whitespace-pre-wrap text-sm">{{ print_r(session('parsed'), true) }}</pre>
                    </div>
                @endif

                <button type="submit" class="oshi-btn">テキスト取り込みする</button>
            </form>
        </div>

        <div class="oshi-card mt-6">
            <h2 class="mb-3 text-xl font-bold">入力例</h2>
            <pre class="whitespace-pre-wrap rounded bg-gray-50 p-4 text-sm">{{ $sampleText }}</pre>
        </div>
    </div>
</x-app-layout>
