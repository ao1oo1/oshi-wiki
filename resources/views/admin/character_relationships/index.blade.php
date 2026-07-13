
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            関係性管理
        </h2>
    </x-slot>

    <div class="p-6">
        @php
            $relationships = $relationships ?? $characterRelationships ?? collect();
        @endphp

        @include('admin.partials.flash')




        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">
                        関係性管理
                    </h1>
                    <p class="oshi-muted">
                        キャラクター同士の呼称や関係性を管理します。
                    </p>
                </div>

                <a href="{{ route('admin.character-relationships.create') }}" class="oshi-btn">
                    関係性登録画面へ
                </a>
                            @if (auth()->user()?->canManageAllAdminFeatures())
                    <a href="{{ route('admin.character-relationships.csv-import.create') }}" class="oshi-btn oshi-btn-sub">
                        CSV取り込み
                    </a>
                
                    <a href="{{ route('admin.character-relationships.csv-export', request()->query()) }}" class="oshi-btn oshi-btn-sub">
                        CSVエクスポート
                    </a>
@endif
</div>

            <form method="GET" action="{{ route('admin.character-relationships.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
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
                        placeholder="呼称・関係性など"
                    >
                </div>

                <div>
                    <label for="work_id" class="mb-1 block font-medium">
                        作品で絞り込み
                    </label>
                    <select id="work_id" name="work_id" class="rounded border-gray-300">
                        <option value="">全作品</option>
                        @foreach (($works ?? collect()) as $work)
                            <option value="{{ $work->id }}" @selected(($selectedWorkId ?? '') == $work->id)>
                                {{ $work->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="oshi-btn">
                    検索・絞り込み
                </button>

                <a href="{{ route('admin.character-relationships.index') }}" class="oshi-btn oshi-btn-sub">
                    解除
                </a>
            </form>

            <form method="POST" action="{{ route('admin.character-relationships.bulk-action') }}" onsubmit="return confirmRelationshipBulkAction();">
                @csrf

                <div class="mb-4 flex flex-wrap items-end gap-3 rounded bg-pink-50 p-4">
                    <div>
                        <label for="relationship_bulk_action" class="mb-1 block font-medium">
                            チェックした関係性を一括操作
                        </label>
                        <select id="relationship_bulk_action" name="bulk_action" class="rounded border-gray-300">
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
                                <th><input type="checkbox" id="relationship_check_all"></th>
                                <th>作品</th>
                                <th>キャラクター</th>
                                <th>相手</th>
                                <th>呼び方</th>
                                <th>関係性</th>
                                <th>状態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($relationships as $relation)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="relationship_ids[]" value="{{ $relation->id }}" class="relationship-checkbox">
                                    </td>

                                    <td>{{ $relation->work?->title ?? '未設定' }}</td>
                                    <td>{{ $relation->fromCharacter?->name ?? '未設定' }}</td>
                                    <td>{{ $relation->toCharacter?->name ?? '未設定' }}</td>
                                    <td>{{ $relation->called_name ?: '未設定' }}</td>
                                    <td>{{ $relation->relationship ?: '未設定' }}</td>

                                    <td>
                                        @include('admin.partials.status-badge', ['status' => $relation->status])
                                    </td>

                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.character-relationships.edit', $relation) }}" class="oshi-btn oshi-btn-sub">
                                                編集
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="oshi-empty">関係性はまだ登録されていません。</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-6">
                {{ $relationships->links() }}
            </div>
        </div>
    </div>

    <script>
        const relationshipCheckAll = document.getElementById('relationship_check_all');

        if (relationshipCheckAll) {
            relationshipCheckAll.addEventListener('change', function () {
                document.querySelectorAll('.relationship-checkbox').forEach(function (checkbox) {
                    checkbox.checked = relationshipCheckAll.checked;
                });
            });
        }

        function confirmRelationshipBulkAction() {
            const checkedCount = document.querySelectorAll('.relationship-checkbox:checked').length;
            const action = document.getElementById('relationship_bulk_action')?.value;

            if (checkedCount === 0) {
                alert('一括操作する関係性を選択してください。');
                return false;
            }

            if (!action) {
                alert('一括操作の内容を選択してください。');
                return false;
            }

            if (action === 'delete') {
                return confirm(checkedCount + '件の関係性に削除フラグを付けます。よろしいですか？');
            }

            return confirm(checkedCount + '件の関係性を一括変更します。よろしいですか？');
        }
    </script>
</x-app-layout>
