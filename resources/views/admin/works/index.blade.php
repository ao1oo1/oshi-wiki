<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            作品管理
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">
                        作品管理
                    </h1>
                    <p class="oshi-muted">
                        作品情報を管理します。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.works.import.create') }}" class="oshi-btn oshi-btn-sub">作品テキスト取込</a>
                    <a href="{{ route('admin.works.csv-import.create') }}" class="oshi-btn oshi-btn-sub">作品CSV取込</a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.works.store') }}" class="mb-8 rounded bg-pink-50 p-4">
                @csrf

                <h2 class="mb-4 text-xl font-bold">
                    作品を新規登録
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="title" class="mb-1 block font-medium">作品名</label>
                        <input id="title" type="text" name="title" value="{{ old('title') }}" class="w-full" required>
                    </div>

                    <div>
                        <label for="title_kana" class="mb-1 block font-medium">読み仮名</label>
                        <input id="title_kana" type="text" name="title_kana" value="{{ old('title_kana') }}" class="w-full">
                    </div>

                    <div>
                        <label for="genre" class="mb-1 block font-medium">ジャンル</label>
                        <input id="genre" type="text" name="genre" value="{{ old('genre') }}" class="w-full">
                    </div>

                    <div>
                        <label for="original_media" class="mb-1 block font-medium">原作媒体</label>
                        <input id="original_media" type="text" name="original_media" value="{{ old('original_media') }}" class="w-full">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block font-medium">タグ</label>

                        @if (($tags ?? collect())->count())
                            <div class="grid grid-cols-1 gap-2 rounded border border-gray-200 bg-white p-3 md:grid-cols-3">
                                @foreach ($tags as $tag)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array($tag->id, old('tag_ids', [])))>
                                        <span>{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="oshi-muted">まだタグが登録されていません。</p>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="mb-1 block font-medium">説明</label>
                        <textarea id="description" name="description" rows="4" class="w-full">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="status" class="mb-1 block font-medium">状態</label>
                        <select id="status" name="status" class="w-full">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>下書き</option>
                            <option value="published" @selected(old('status') === 'published')>公開</option>
                            <option value="private" @selected(old('status') === 'private')>非公開</option>
                        </select>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="oshi-btn">
                        作品を登録する
                    </button>
                </div>
            </form>

            <form method="GET" action="{{ route('admin.works.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
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

                <button type="submit" class="oshi-btn">
                    検索・絞り込み
                </button>

                <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">
                    解除
                </a>
            </form>

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

                <div class="oshi-table-wrap">
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
                                            <a href="{{ route('admin.works.edit', $work) }}" class="oshi-btn oshi-btn-sub">編集</a>
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

            <div class="mt-6">
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
