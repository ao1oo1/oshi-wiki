<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">作品商品リンク管理</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-[#718096]">作品ID：{{ $work->id }}</p>
                <h1 class="text-2xl font-bold text-[#2D3748]">
                    {{ $work->title }}の商品リンク管理
                </h1>
            </div>
            <a
                href="{{ route('admin.works.show', $work) }}"
                class="oshi-btn oshi-btn-sub"
            >
                作品詳細へ戻る
            </a>
        </div>

        <section class="oshi-card mb-6">
            <h2 class="mb-2 text-xl font-bold text-[#2D3748]">
                作品側の収益化設定
            </h2>
            <p class="mb-5 text-sm leading-7 text-[#718096]">
                商品リンクの登録とは別に、公開画面へ表示するか、
                親作品のリンクを引き継ぐかを設定します。
            </p>

            <form
                method="POST"
                action="{{ route('admin.works.monetization-settings.update', $work) }}"
            >
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="monetization_enabled" class="oshi-label">
                            収益リンク表示
                        </label>
                        <select
                            id="monetization_enabled"
                            name="monetization_enabled"
                            class="oshi-input"
                        >
                            <option value="1" @selected((string) old('monetization_enabled', (int) $work->monetization_enabled) === '1')>表示する</option>
                            <option value="0" @selected((string) old('monetization_enabled', (int) $work->monetization_enabled) === '0')>表示しない</option>
                        </select>
                    </div>

                    <div>
                        <label for="monetization_inheritance" class="oshi-label">
                            親子作品のリンク継承
                        </label>
                        <select
                            id="monetization_inheritance"
                            name="monetization_inheritance"
                            class="oshi-input"
                        >
                            @foreach ($inheritanceOptions as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        old(
                                            'monetization_inheritance',
                                            $work->monetization_inheritance
                                        ) === $value
                                    )
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @if ($work->parentWork)
                            <p class="mt-2 text-xs text-[#718096]">
                                親作品：{{ $work->parentWork->title }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="isbn" class="oshi-label">ISBN</label>
                        <input
                            id="isbn"
                            type="text"
                            name="isbn"
                            value="{{ old('isbn', $work->isbn) }}"
                            class="oshi-input"
                        >
                    </div>

                    <div>
                        <label for="official_store_url" class="oshi-label">
                            公式販売URL
                        </label>
                        <input
                            id="official_store_url"
                            type="url"
                            name="official_store_url"
                            value="{{ old(
                                'official_store_url',
                                $work->official_store_url
                            ) }}"
                            class="oshi-input"
                        >
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="oshi-btn">
                        作品設定を更新する
                    </button>
                </div>
            </form>
        </section>

        <section class="oshi-card mb-6">
            <h2 class="mb-2 text-xl font-bold text-[#2D3748]">
                商品リンクを新規登録
            </h2>
            <p class="mb-5 text-sm leading-7 text-[#718096]">
                完成URLではなく商品コードを登録します。
                URLは提携プログラムのテンプレートから自動生成されます。
            </p>

            @if ($programs->isEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-800">
                    有効な提携プログラムがありません。先に収益管理から登録してください。
                </div>
            @else
                <form
                    method="POST"
                    action="{{ route('admin.works.monetization-links.store', $work) }}"
                >
                    @include('admin.monetization.work-links._form')
                    <div class="mt-6">
                        <button type="submit" class="oshi-btn">
                            商品リンクを登録する
                        </button>
                    </div>
                </form>
            @endif
        </section>

        <section class="oshi-card">
            <h2 class="mb-5 text-xl font-bold text-[#2D3748]">
                登録済み商品リンク
            </h2>

            <div class="overflow-x-auto rounded-3xl border border-[#E2E8F0]">
                <table class="w-full min-w-[1100px] text-left text-sm">
                    <thead class="bg-[#FFF5F7] text-[#2D3748]">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">サービス</th>
                            <th class="px-4 py-3">商品</th>
                            <th class="px-4 py-3">コード</th>
                            <th class="px-4 py-3 text-center">提供状況</th>
                            <th class="px-4 py-3">最終検証</th>
                            <th class="px-4 py-3 text-center">状態</th>
                            <th class="px-4 py-3 text-center">優先順位</th>
                            <th class="px-4 py-3 text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($links as $link)
                            <tr class="border-t border-[#E2E8F0]">
                                <td class="px-4 py-4">{{ $link->id }}</td>
                                <td class="px-4 py-4">
                                    <p class="font-bold">
                                        {{ $link->service?->name }}
                                    </p>
                                    <p class="mt-1 text-xs text-[#718096]">
                                        {{ $link->affiliateProgram?->name }}
                                    </p>
                                </td>
                                <td class="px-4 py-4">
                                    {{ $link->title ?: ($productTypes[$link->product_type] ?? $link->product_type) }}
                                </td>
                                <td class="px-4 py-4 font-mono text-xs">
                                    {{ $link->product_code }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    {{ $availabilityStatuses[$link->availability_status] ?? $link->availability_status }}
                                </td>
                                <td class="px-4 py-4 text-xs leading-6">
                                    <p>
                                        {{ $link->last_verified_at?->format('Y-m-d H:i') ?: '未検証' }}
                                    </p>
                                    @if ($link->verification_note)
                                        <p class="mt-1 max-w-xs text-[#718096]">
                                            {{ $link->verification_note }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    {{ $link->is_active ? '有効' : '無効' }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    {{ $link->priority }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <form
                                            method="POST"
                                            action="{{ route('admin.works.monetization-links.verify', [$work, $link]) }}"
                                        >
                                            @csrf
                                            <button type="submit" class="oshi-btn oshi-btn-sub">
                                                リンク確認
                                            </button>
                                        </form>
                                        <a
                                            href="{{ route('admin.works.monetization-links.edit', [$work, $link]) }}"
                                            class="oshi-btn oshi-btn-sub"
                                        >
                                            編集
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.works.monetization-links.destroy', [$work, $link]) }}"
                                            onsubmit="return confirm('この商品リンクを削除しますか？');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="oshi-btn oshi-btn-danger">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-[#718096]">
                                    商品リンクは登録されていません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
