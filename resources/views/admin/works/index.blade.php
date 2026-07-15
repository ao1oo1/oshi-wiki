<x-app-layout>
    @php
        $canManageWorks = auth()->user()?->canManageAllAdminFeatures() ?? false;
    @endphp
    <x-slot name="header">
<h2 class="font-semibold text-xl">
            作品管理
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card admin-index-shell">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 admin-index-header">
                <div>
                    <h1 class="text-2xl font-bold">
                        作品管理
                    </h1>
                    <p class="oshi-muted">
                        作品情報を管理します。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 admin-index-actions">
                    @if ($canManageWorks)
<a href="{{ route('admin.works.import.create') }}" class="oshi-btn oshi-btn-sub">テキスト取り込み</a>

                    @endif
                    @if ($canManageWorks)

                        <a href="{{ route('admin.works.csv-import.create') }}" class="oshi-btn oshi-btn-sub">CSV取り込み</a>


                        @if (auth()->user()?->canManageAllAdminFeatures())
                            <a href="{{ route('admin.works.csv-export', request()->query()) }}" class="oshi-btn oshi-btn-sub">
                                CSVエクスポート
                            </a>
                        @endif
@endif
                </div>
            </div>

            {{-- STAFF_HIDE_WORK_CREATE_FORM_FIX --}}
@if ($canManageWorks)
    <div class="mb-8 rounded-2xl border border-pink-200 bg-pink-50 p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold">作品を新規登録</h2>
                <p class="mt-1 text-sm text-gray-600">
                    基本情報、物語の時間軸、舞台、生活ルール、行事、用語などをカテゴリごとに登録できます。
                </p>
            </div>
            <a href="{{ route('admin.works.create') }}" class="oshi-btn oshi-btn-main">
                作品登録画面へ
            </a>
        </div>
    </div>
@endif
{{-- /STAFF_HIDE_WORK_CREATE_FORM_FIX --}}
</div>

            <form method="GET" action="{{ route('admin.works.index') }}" class="admin-index-filter-form">
                <div class="admin-index-filter-grid">
                <div>
                    <label for="keyword" class="mb-1 block font-medium">
                        キーワード
                    </label>
                    <input
                        id="keyword"
                        type="text"
                        name="keyword"
                        value="{{ $keyword ?? '' }}"
                        class="rounded border-gray-300"
                        placeholder="作品名・ジャンルなど"
                    >
                </div>

                <div>
                    <label for="tag_id" class="mb-1 block font-medium">
                        タグで絞り込み
                    </label>
                    <select id="tag_id" name="tag_id" class="rounded border-gray-300">
                        <option value="">全タグ</option>
                        @foreach (($tags ?? collect()) as $tag)
                            <option value="{{ $tag->id }}" @selected(($selectedTagId ?? '') == $tag->id)>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @include('admin.partials.list-search-extra')


                <button type="submit" class="oshi-btn">
                    検索・絞り込み
                </button>

                <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">
                    解除
                </a>
            </div>
            </form>

            @include('admin.works._staff_mobile_cards')


            @if ($canManageWorks)


            @if ($canManageWorks)



            <form method="POST" action="{{ route('admin.works.bulk-action') }}" onsubmit="return confirmWorkBulkAction();">
                @csrf

                <div class="mb-4 flex flex-wrap items-end gap-3 rounded bg-pink-50 p-4">
                    <div>
                        <label for="work_bulk_action" class="mb-1 block font-medium">
                            チェックした作品を一括操作
                        </label>
                        <select id="work_bulk_action" name="bulk_action" class="rounded border-gray-300">
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

                <div class="staff-work-mobile-table-shell oshi-table-wrap">
                    <table class="oshi-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="work_check_all"></th>
                                <th>作品名</th>
                                <th>ジャンル</th>
                                <th>原作媒体</th>
                                <th>状態</th>
                                <th>タグ</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($works as $work)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="work_ids[]" value="{{ $work->id }}" class="work-checkbox">
                                    </td>

                                    <td>
                                        <div class="font-bold">{{ $work->title }}</div>
                                        @if ($work->title_kana)
                                            <div class="oshi-muted">{{ $work->title_kana }}</div>
                                        @endif
                                    </td>

                                    <td>{{ $work->genre ?: '未設定' }}</td>
                                    <td>{{ $work->original_media ?: '未設定' }}</td>

                                    <td>
                                        @include('admin.partials.status-badge', ['status' => $work->status])
                                    </td>

                                    <td>
                                        @if ($work->tags->count())
                                            @foreach ($work->tags as $tag)
                                                <span class="oshi-chip">{{ $tag->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="oshi-muted">未設定</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.works.show', $work) }}" class="oshi-btn oshi-btn-sub">詳細</a>
                                            @if ($canManageWorks)

                                                <a href="{{ route('admin.works.edit', $work) }}" class="oshi-btn oshi-btn-sub">編集</a>

                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="oshi-empty">作品はまだ登録されていません。</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


            </form>



            @endif


            @endif

            <div class="mt-6">

{{-- STAFF_WORK_LIST_VISIBLE_FIX --}}
@if (! $canManageWorks)
    <div class="mt-6 overflow-x-auto rounded-3xl border border-[#E2E8F0] bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#FFF5F7] text-[#2D3748]">
                <tr>
                    <th class="p-4 font-bold">作品名</th>
                    <th class="p-4 font-bold">ジャンル</th>
                    <th class="p-4 font-bold">原作媒体</th>
                    <th class="p-4 font-bold">タグ</th>
                    <th class="p-4 font-bold">状態</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($works as $work)
                    <tr class="border-t border-[#E2E8F0]">
                        <td class="p-4 align-top font-bold text-[#2D3748]">
                            @if (Route::has('admin.works.show'))
                                <a href="{{ route('admin.works.show', $work) }}" class="text-[#2D3748] underline-offset-4 hover:underline">
                                    {{ $work->title }}
                                </a>
                            @else
                                {{ $work->title }}
                            @endif
                        </td>
                        <td class="p-4 align-top text-[#4A5568]">
                            {{ $work->genre ?: '—' }}
                        </td>
                        <td class="p-4 align-top text-[#4A5568]">
                            {{ $work->original_media ?: '—' }}
                        </td>
                        <td class="p-4 align-top text-[#4A5568]">
                            @if ($work->tags && $work->tags->count())
                                {{ $work->tags->pluck('name')->join('、') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-4 align-top text-[#4A5568]">
                            {{ $work->status ?: '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-[#718096]">
                            作品が登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
{{-- /STAFF_WORK_LIST_VISIBLE_FIX --}}




                {{ $works->links() }}
            </div>
        </div>
    </div>

    <script>
        const workCheckAll = document.getElementById('work_check_all');

        if (workCheckAll) {
            workCheckAll.addEventListener('change', function () {
                document.querySelectorAll('.work-checkbox').forEach(function (checkbox) {
                    checkbox.checked = workCheckAll.checked;
                });
            });
        }

        function confirmWorkBulkAction() {
            const checkedCount = document.querySelectorAll('.work-checkbox:checked').length;
            const action = document.getElementById('work_bulk_action')?.value;

            if (checkedCount === 0) {
                alert('一括操作する作品を選択してください。');
                return false;
            }

            if (!action) {
                alert('一括操作の内容を選択してください。');
                return false;
            }

            if (action === 'delete') {
                return confirm(checkedCount + '件の作品に削除フラグを付けます。紐づくキャラクターや関係性にも影響する場合があります。よろしいですか？');
            }

            return confirm(checkedCount + '件の作品を一括変更します。よろしいですか？');
        }
    </script>
</x-app-layout>
