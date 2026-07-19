
<x-app-layout>
    @php
        $canManageTags = auth()->user()?->canManageAllAdminFeatures() ?? false;
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            タグ管理
        </h2>
    </x-slot>
    @php
        $adminListTotalCount = \App\Models\Tag::query()->count();
    @endphp

    <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8"
         data-admin-result-count>
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <p class="text-sm font-semibold text-slate-700">
                検索結果
                <span class="text-base text-slate-900">{{ number_format($tags->total()) }}</span>件
                <span class="mx-1 text-slate-400">／</span>
                全体
                <span class="text-base text-slate-900">{{ number_format($adminListTotalCount) }}</span>件
            </p>
        </div>
    </div>


    <div class="p-6">
        @include('admin.partials.flash')
        @include('admin.partials.publish-help')


{{-- STAFF_TAG_LIST_VISIBLE_FIX --}}
@if (! $canManageTags)
    <div class="mb-6 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-xl font-bold text-[#2D3748]">
                タグ一覧
            </h2>
            <p class="mt-1 text-sm font-bold text-[#A0AEC0]">
                登録済みタグを確認できます。
            </p>
        </div>

        <form method="GET" action="{{ route('admin.tags.index') }}" class="mb-6 rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5 admin-index-filter-form">
                <div class="admin-index-filter-grid">
                <div>
                    <label for="staff_type" class="mb-1 block text-sm font-bold text-[#4A5568]">
                        種類
                    </label>
                    <select id="staff_type" name="type" class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3">
                        <option value="">すべて</option>
                        @foreach (($tagTypes ?? collect()) as $tagType)
                            <option value="{{ $tagType }}" @selected(($selectedType ?? request('type')) === $tagType)>
                                {{ $tagType }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="staff_keyword" class="mb-1 block text-sm font-bold text-[#4A5568]">
                        キーワード
                    </label>
                    <input
                        id="staff_keyword"
                        type="text"
                        name="keyword"
                        value="{{ $keyword ?? request('keyword') }}"
                        placeholder="タグ名・説明など"
                        class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                    >
                </div>

                @include('admin.partials.list-search-extra')


                <button type="submit" class="oshi-btn">
                    検索・絞り込み
                </button>

                <a href="{{ route('admin.tags.index') }}" class="oshi-btn oshi-btn-sub text-center">
                    解除
                </a>
            </div>
            </form>

        <div class="staff-tag-mobile-table-shell overflow-x-auto rounded-3xl border border-[#E2E8F0]">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF5F7] text-[#2D3748]">
                    <tr>
                                <th class="whitespace-nowrap px-3 py-3 text-left text-xs font-semibold"
                                    data-admin-id-column>ID</th>
                        <th class="p-4 font-bold">タグ名</th>
                        <th class="p-4 font-bold">種類</th>
                        <th class="p-4 font-bold">説明</th>
                        <th class="p-4 font-bold admin-index-status-head">状態</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tags as $tag)
                        <tr class="border-t border-[#E2E8F0]">
                                    <td class="whitespace-nowrap px-3 py-3 text-sm font-semibold text-slate-600"
                                        data-admin-id-value>{{ $tag->id }}</td>
                            <td class="p-4 align-top font-bold text-[#2D3748]">
                                {{ $tag->name }}
                            </td>
                            <td class="p-4 align-top text-[#4A5568]">
                                {{ $tag->type ?: '—' }}
                            </td>
                            <td class="p-4 align-top text-[#4A5568]">
                                {{ $tag->description ?: '—' }}
                            </td>
                            <td class="p-4 align-top text-[#4A5568]">
                                {{ $tag->status ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-[#718096]">
                                タグが登録されていません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.tags._staff_mobile_cards')

        <div class="mt-6">
            {{ $tags->links() }}
        </div>
    </div>
@endif
{{-- /STAFF_TAG_LIST_VISIBLE_FIX --}}


        {{-- STAFF_HIDE_TAG_HEADER_CARD_FIX --}}
@if ($canManageTags)
<div class="oshi-card admin-index-shell">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 admin-index-header">
                <div>
                    <h1 class="text-2xl font-bold">
                        タグ管理
                    </h1>
                    <p class="oshi-muted">
                        作品やキャラクターに付与するタグを管理します。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 admin-index-actions">
                    @if ($canManageTags)
<a href="{{ route('admin.tags.import.create') }}" class="oshi-btn oshi-btn-sub">テキスト取り込み</a>

                    @endif
                    @if ($canManageTags)

                        <a href="{{ route('admin.tags.csv-import.create') }}" class="oshi-btn oshi-btn-sub">CSV取り込み</a>


                        @if (auth()->user()?->canManageAllAdminFeatures())
                            <a href="{{ route('admin.tags.csv-export', request()->query()) }}" class="oshi-btn oshi-btn-sub">
                                CSVエクスポート
                            </a>
                        @endif
@endif
                </div>
            </div>

            {{-- STAFF_HIDE_TAG_CREATE_FORM_FIX --}}
@if ($canManageTags)
<form method="POST" action="{{ route('admin.tags.store') }}" class="mb-8 rounded bg-pink-50 p-4 oshi-u-index-create-form">
                @csrf

                <div class="oshi-tag-index-create-section {{ request()->query('show_create') ? 'is-mobile-create-open' : '' }}">
<h2 class="mb-4 text-xl font-bold">
                    タグを新規登録
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1 block font-medium">タグ名</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="w-full"
                            required
                        >
                    </div>

                    <div>
                        <label for="type" class="mb-1 block font-medium">種類</label>
                        <input
                            id="type"
                            type="text"
                            name="type"
                            value="{{ old('type', 'general') }}"
                            class="w-full"
                        >
                    </div>

                    <div>
                        <label for="status" class="mb-1 block font-medium">状態</label>
                        <p class="mb-2 text-sm text-gray-600">
                            公開ページの絞り込みに使う場合は「公開」を選択してください。
                        </p>
                        <select id="status" name="status" class="w-full">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>下書き</option>
                            <option value="published" @selected(old('status') === 'published')>公開</option>
                            <option value="private" @selected(old('status') === 'private')>非公開</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="mb-1 block font-medium">説明</label>
                        <textarea id="description" name="description" rows="4" class="w-full">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="oshi-btn">
                        タグを登録する
                    </button>
                </div>
            </form>
@endif
{{-- /STAFF_HIDE_TAG_CREATE_FORM_FIX --}}
</div>

            @if ($canManageTags)



            <form method="GET" action="{{ route('admin.tags.index') }}" class="mb-6 rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5 admin-index-filter-form">
                <div class="admin-index-filter-grid">
                    <div>
                        <label for="type" class="mb-1 block text-sm font-bold text-[#4A5568]">
                            種類
                        </label>
                        <select id="type" name="type" class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3">
                            <option value="">すべて</option>
                            @foreach (($tagTypes ?? collect()) as $tagType)
                                <option value="{{ $tagType }}" @selected(($selectedType ?? request('type')) === $tagType)>
                                    {{ $tagType }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="keyword" class="mb-1 block text-sm font-bold text-[#4A5568]">
                            検索ワード
                        </label>
                        <input
                            id="keyword"
                            type="text"
                            name="keyword"
                            value="{{ $keyword ?? request('keyword') }}"
                            placeholder="タグ名・slug・説明などで検索"
                            class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                        >
                    </div>



                @include('admin.partials.list-search-extra')

                <button type="submit" class="oshi-btn">
                        検索
                    </button>

                    <a href="{{ route('admin.tags.index') }}" class="oshi-btn oshi-btn-sub text-center">
                        クリア
                    </a>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.tags.bulk-action') }}" onsubmit="return confirmTagBulkAction();">
                @csrf

                <div class="mb-4 flex flex-wrap items-end gap-3 rounded bg-pink-50 p-4">
                    <div>
                        <label for="tag_bulk_action" class="mb-1 block font-medium">
                            チェックしたタグを一括操作
                        </label>
                        <select id="tag_bulk_action" name="bulk_action" class="rounded border-gray-300">
                            <option value="">選択してください</option>
                            <option value="publish">公開にする</option>
                            <option value="private">非公開にする</option>
                            <option value="delete">削除フラグをつける</option>
                        </select>
                    </div>

                    <button type="submit" class="oshi-btn">
                        一括反映
                    </button>

                    <p class="text-sm text-gray-600">
                        削除は完全削除ではなく、削除フラグを付ける処理です。
                    </p>
                </div>

                <div class="oshi-table-wrap">
                    <table class="oshi-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="tag_check_all"></th>
                                <th>タグ名</th>
                                <th>種類</th>
                                <th>説明</th>
                                <th  class="admin-index-status-head">状態</th>
                                <th  class="admin-index-action-head">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tags as $tag)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="tag-checkbox">
                                    </td>

                                    <td>
                                        <strong>{{ $tag->name }}</strong>
                                        <div class="oshi-muted">
                                            {{ $tag->slug }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ $tag->type ?: 'general' }}
                                    </td>

                                    <td>
                                        {{ $tag->description ?: '未設定' }}
                                    </td>

                                    <td class="admin-index-status-cell">
                                        @include('admin.partials.status-badge', ['status' => $tag->status])
                                    </td>

                                    <td class="admin-index-action-cell">
                                        @if ($canManageTags)

                                            <a href="{{ route('admin.tags.edit', $tag) }}" class="oshi-btn oshi-btn-sub">編集</a>

                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="oshi-empty">
                                            タグはまだ登録されていません。
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>


            @endif

            <div class="mt-6">
                {{ $tags->links() }}
            </div>
        </div>
    </div>

    <script>
        const tagCheckAll = document.getElementById('tag_check_all');

        if (tagCheckAll) {
            tagCheckAll.addEventListener('change', function () {
                document.querySelectorAll('.tag-checkbox').forEach(function (checkbox) {
                    checkbox.checked = tagCheckAll.checked;
                });
            });
        }

        function confirmTagBulkAction() {
            const checkedCount = document.querySelectorAll('.tag-checkbox:checked').length;
            const action = document.getElementById('tag_bulk_action')?.value;

            if (checkedCount === 0) {
                alert('一括操作するタグを選択してください。');
                return false;
            }

            if (!action) {
                alert('一括操作の内容を選択してください。');
                return false;
            }

            if (action === 'delete') {
                return confirm(checkedCount + '件のタグに削除フラグを付けます。よろしいですか？');
            }

            return confirm(checkedCount + '件のタグを一括変更します。よろしいですか？');
        }
    </script>
@endif
{{-- /STAFF_HIDE_TAG_HEADER_CARD_FIX --}}

</x-app-layout>
