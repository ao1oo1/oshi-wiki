<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">ゴミ箱</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.navigation')

        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-bold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="oshi-card">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">ゴミ箱</h1>
                <p class="oshi-muted">
                    削除フラグが付いたデータを確認し、データベースから完全削除できます。
                    完全削除したデータは元に戻せません。
                </p>
            </div>

            <div class="mb-6 flex flex-wrap gap-2">
                @foreach ($types as $key => $config)
                    <a
                        href="{{ route('admin.trash.index', ['type' => $key]) }}"
                        class="{{ $type === $key ? 'oshi-btn' : 'oshi-btn oshi-btn-sub' }}"
                    >
                        {{ $config['label'] }}（{{ $counts[$key] ?? 0 }}）
                    </a>
                @endforeach
            </div>

            <div class="mb-6 rounded-3xl border-2 border-red-200 bg-red-50 p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-red-800">
                            ゴミ箱内の全データを完全削除
                        </h2>
                        <p class="mt-1 text-sm font-medium text-red-700">
                            作品・キャラクター・関係性・タグの削除フラグ付きデータ、
                            合計{{ $totalDeletedCount ?? array_sum($counts) }}件をすべて完全削除します。
                            この操作は元に戻せません。
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.trash.destroy-all') }}"
                        onsubmit="return confirmTrashDestroyAll({{ $totalDeletedCount ?? array_sum($counts) }});"
                        class="shrink-0"
                    >
                        @csrf
                        <button
                            type="submit"
                            class="oshi-btn w-full bg-red-700 text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50 lg:w-auto"
                            @disabled(($totalDeletedCount ?? array_sum($counts)) === 0)
                        >
                            ゴミ箱内の全データを完全削除
                        </button>
                    </form>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.trash.index') }}" class="mb-6 rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5">
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="grid gap-4 md:grid-cols-[1fr_auto_auto] md:items-end">
                    <div>
                        <label for="keyword" class="mb-1 block text-sm font-bold text-[#4A5568]">
                            検索ワード
                        </label>
                        <input
                            id="keyword"
                            type="text"
                            name="keyword"
                            value="{{ $keyword }}"
                            placeholder="名称・状態などで検索"
                            class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                        >
                    </div>

                    <button type="submit" class="oshi-btn">
                        検索
                    </button>

                    <a href="{{ route('admin.trash.index', ['type' => $type]) }}" class="oshi-btn oshi-btn-sub text-center">
                        クリア
                    </a>
                </div>
            </form>

            <form
                id="trash-bulk-form"
                method="POST"
                action="{{ route('admin.trash.bulk-destroy') }}"
                onsubmit="return confirm('選択したデータをデータベースから完全削除します。元に戻せません。よろしいですか？');"
            >
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
            </form>

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="font-bold text-[#4A5568]">
                    {{ $types[$type]['label'] }}：{{ $items->total() }}件
                </div>

                <button type="submit" form="trash-bulk-form" class="oshi-btn bg-red-600 text-white hover:opacity-90">
                    チェックしたデータを完全削除
                </button>
            </div>

            <div class="overflow-x-auto rounded-3xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FFF5F7]">
                            <tr>
                                <th class="p-4">
                                    <input type="checkbox" onclick="document.querySelectorAll('.trash-check').forEach(el => el.checked = this.checked)">
                                </th>
                                <th class="p-4">ID</th>
                                <th class="p-4">名称</th>
                                <th class="p-4">概要</th>
                                <th class="p-4">削除日時</th>
                                <th class="p-4">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr class="border-t border-[#E2E8F0]">
                                    <td class="p-4 align-top">
                                        <input class="trash-check" form="trash-bulk-form" type="checkbox" name="ids[]" value="{{ $item->id }}">
                                    </td>
                                    <td class="p-4 align-top font-bold">
                                        {{ $item->id }}
                                    </td>
                                    <td class="p-4 align-top font-bold">
                                        {{ \App\Http\Controllers\Admin\TrashController::displayName($item, $type) }}
                                    </td>
                                    <td class="p-4 align-top text-[#718096]">
                                        {{ \App\Http\Controllers\Admin\TrashController::summary($item, $type) ?: '—' }}
                                    </td>
                                    <td class="p-4 align-top">
                                        {{ optional($item->deleted_at)->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="p-4 align-top">
                                        <form
                                            method="POST"
                                            action="{{ route('admin.trash.destroy', [$type, $item->id]) }}"
                                            onsubmit="return confirm('このデータをデータベースから完全削除します。元に戻せません。よろしいですか？');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="oshi-btn oshi-btn-sub text-red-600">
                                                完全削除
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-[#718096]">
                                        ゴミ箱にデータはありません。
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            <div class="mt-6">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <script>
        function confirmTrashDestroyAll(totalCount) {
            if (totalCount <= 0) {
                return false;
            }

            const firstConfirmed = window.confirm(
                'ゴミ箱内の削除フラグ付きデータ' + totalCount
                + '件をすべて完全削除します。\n'
                + '作品・キャラクター・関係性・タグが対象です。\n'
                + 'この操作は元に戻せません。続行しますか？'
            );

            if (! firstConfirmed) {
                return false;
            }

            return window.confirm(
                '最終確認です。ゴミ箱内の全データをデータベースから完全削除します。'
            );
        }
    </script>
</x-app-layout>
