
<x-app-layout>
    @php
        $canUseCharacterImports = auth()->user()?->canManageAllAdminFeatures() ?? false;
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            キャラクター管理
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">
                        キャラクター管理
                    </h1>
                    <p class="oshi-muted">
                        作品ごとのキャラクター情報を管理します。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($canUseCharacterImports)

                        <a href="{{ route('admin.characters.import.create') }}" class="oshi-btn oshi-btn-sub">テキスト取り込み</a>

                    @endif

                    @if ($canUseCharacterImports)


                        <a href="{{ route('admin.characters.csv-import.create') }}" class="oshi-btn oshi-btn-sub">CSV取り込み</a>


                    
                        @if (auth()->user()?->canManageAllAdminFeatures())
                            <a href="{{ route('admin.characters.csv-export', request()->query()) }}" class="oshi-btn oshi-btn-sub">
                                CSVエクスポート
                            </a>
                        @endif
@endif

                    <a href="{{ route('admin.characters.create') }}" class="oshi-btn">
                        キャラクター登録画面へ
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.characters.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
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
                        placeholder="名前・所属・性格など"
                    >
                </div>

                <div>
                    <label for="work_id" class="mb-1 block font-medium">
                        作品で絞り込み
                    </label>
                    <select id="work_id" name="work_id" class="rounded border-gray-300">
                        <option value="">全作品</option>
                        @foreach ($works as $work)
                            <option value="{{ $work->id }}" @selected(($selectedWorkId ?? '') == $work->id)>
                                {{ $work->title }}
                            </option>
                        @endforeach
                    </select>
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

                <a href="{{ route('admin.characters.index') }}" class="oshi-btn oshi-btn-sub">
                    解除
                </a>
            </form>

            @if ($canUseCharacterImports)


            <form method="POST" action="{{ route('admin.characters.bulk-action') }}" onsubmit="return confirmBulkAction();">
                @csrf

                <div class="mb-4 flex flex-wrap items-end gap-3 rounded bg-pink-50 p-4">
                    <div>
                        <label for="bulk_action" class="mb-1 block font-medium">
                            チェックした項目を一括操作
                        </label>
                        <select id="bulk_action" name="bulk_action" class="rounded border-gray-300">
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
                                <th>
                                    <input type="checkbox" id="check_all">
                                </th>
                                <th>名前</th>
                                <th>作品</th>
                                <th>年齢</th>
                                <th>所属</th>
                                <th>一人称</th>
                                <th>状態</th>
                                <th>タグ</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($characters as $character)
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            name="character_ids[]"
                                            value="{{ $character->id }}"
                                            class="character-checkbox"
                                        >
                                    </td>

                                    <td>
                                        <div class="font-bold">
                                            {{ $character->name }}
                                        </div>

                                        @if ($character->name_kana)
                                            <div class="oshi-muted">
                                                {{ $character->name_kana }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $character->work?->title ?? '未設定' }}
                                    </td>

                                    <td>
                                        {{ $character->age ?: '未設定' }}
                                    </td>

                                    <td>
                                        {{ $character->affiliation ?: '未設定' }}
                                    </td>

                                    <td>
                                        {{ $character->first_person ?: '未設定' }}
                                    </td>

                                    <td>
                                        @include('admin.partials.status-badge', ['status' => $character->status])
                                    </td>

                                    <td>
                                        @if ($character->tags->count())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($character->tags as $tag)
                                                    <span class="oshi-chip">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="oshi-muted">未設定</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.characters.show', $character) }}" class="oshi-btn oshi-btn-sub">
                                                詳細
                                            </a>

                                            <a href="{{ route('admin.characters.edit', $character) }}" class="oshi-btn oshi-btn-sub">
                                                編集
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="oshi-empty">
                                            キャラクターはまだ登録されていません。
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
                {{ $characters->links() }}
            </div>
        </div>
    </div>

    <script>
        const checkAll = document.getElementById('check_all');

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                document.querySelectorAll('.character-checkbox').forEach(function (checkbox) {
                    checkbox.checked = checkAll.checked;
                });
            });
        }

        function confirmBulkAction() {
            const checkedCount = document.querySelectorAll('.character-checkbox:checked').length;
            const action = document.getElementById('bulk_action')?.value;

            if (checkedCount === 0) {
                alert('一括操作するキャラクターを選択してください。');
                return false;
            }

            if (!action) {
                alert('一括操作の内容を選択してください。');
                return false;
            }

            if (action === 'delete') {
                return confirm(checkedCount + '件のキャラクターに削除フラグを付けます。よろしいですか？');
            }

            return confirm(checkedCount + '件のキャラクターを一括変更します。よろしいですか？');
        }
    </script>
</x-app-layout>
