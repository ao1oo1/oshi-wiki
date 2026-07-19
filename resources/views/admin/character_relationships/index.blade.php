<x-app-layout>
    @php
        $relationships = $relationships ?? $characterRelationships ?? collect();

        $currentUser = auth()->user();
        $canManageRelationships = $currentUser?->canManageAllAdminFeatures() ?? false;
        $canCreateRelationships = $canManageRelationships || ($currentUser?->isStaff() ?? false);

        $selectedWorkId = $selectedWorkId ?? request('work_id');
        $keyword = $keyword ?? request('keyword');
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            関係性管理
        </h2>
    </x-slot>
    @php
        $adminListTotalCount = \App\Models\CharacterRelationship::query()->count();
    @endphp

    <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8"
         data-admin-result-count>
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <p class="text-sm font-semibold text-slate-700">
                検索結果
                <span class="text-base text-slate-900">{{ number_format($relationships->total()) }}</span>件
                <span class="mx-1 text-slate-400">／</span>
                全体
                <span class="text-base text-slate-900">{{ number_format($adminListTotalCount) }}</span>件
            </p>
        </div>
    </div>


    <div class="p-6">
        @include('admin.partials.flash')
        @include('admin.partials.publish-help')

        <div class="oshi-card admin-index-shell">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4 admin-index-header">
                <div>
                    <h1 class="text-2xl font-bold text-[#2D3748]">
                        関係性管理
                    </h1>
                    <p class="oshi-muted">
                        キャラクター同士の呼称や関係性を管理します。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 admin-index-actions">
                    @if ($canManageRelationships)
                        <a href="{{ route('admin.character-relationships.csv-import.create') }}" class="oshi-btn oshi-btn-sub">
                            CSV取り込み
                        </a>
                        <a href="{{ route('admin.character-relationships.csv-export', request()->query()) }}" class="oshi-btn oshi-btn-sub">
                            CSVエクスポート
                        </a>
                    @endif

                    @if ($canCreateRelationships)
                        <a href="{{ route('admin.character-relationships.create') }}" class="oshi-btn">
                            関係性登録画面へ
                        </a>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('admin.character-relationships.index') }}" class="mb-6 admin-index-filter-form">
                <div class="admin-index-filter-grid">
                    <div>
                        <label for="keyword" class="mb-1 block text-sm font-bold text-[#4A5568]">
                            キーワード
                        </label>
                        <input
                            id="keyword"
                            type="text"
                            name="keyword"
                            value="{{ $keyword }}"
                            placeholder="呼称・関係性など"
                            class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                        >
                    </div>

                    <div>
                        <label for="work_id" class="mb-1 block text-sm font-bold text-[#4A5568]">
                            作品で絞り込み
                        </label>
                        <select
                            id="work_id"
                            name="work_id"
                            class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                        >
                            <option value="">全作品</option>
                            @foreach (($works ?? collect()) as $work)
                                <option value="{{ $work->id }}" @selected((string) $selectedWorkId === (string) $work->id)>
                                    {{ $work->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                @include('admin.partials.list-search-extra')


                    <button type="submit" class="oshi-btn">
                        検索・絞り込み
                    </button>

                    <a href="{{ route('admin.character-relationships.index') }}" class="oshi-btn oshi-btn-sub text-center">
                        解除
                    </a>
                </div>
            </form>

            @if ($canManageRelationships)
                <form
                    id="relationship-bulk-form"
                    method="POST"
                    action="{{ route('admin.character-relationships.bulk-action') }}"
                    onsubmit="return confirmRelationshipBulkAction();"
                    class="mb-6 rounded-3xl bg-[#FFF5F7] p-5"
                >
                    @csrf

                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label for="relationship_bulk_action" class="mb-1 block text-sm font-bold text-[#4A5568]">
                                チェックした関係性を一括操作
                            </label>
                            <select
                                id="relationship_bulk_action"
                                name="bulk_action"
                                class="rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                                required
                            >
                                <option value="">選択してください</option>
                                <option value="publish">公開にする</option>
                                <option value="private">非公開にする</option>
                                <option value="delete">削除</option>
                            </select>
                        </div>

                        <button type="submit" class="oshi-btn">
                            一括反映
                        </button>

                        <p class="text-sm font-bold text-[#A0AEC0]">
                            削除は完全削除ではなく、削除フラグを付ける処理です。
                        </p>
                    </div>
                </form>
            @endif

            <div class="overflow-hidden rounded-3xl border border-[#E2E8F0] bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-[#FFF5F7] text-[#2D3748]">
                            <tr>
                                <th class="whitespace-nowrap px-3 py-3 text-left text-xs font-semibold"
                                    data-admin-id-column>ID</th>
                                @if ($canManageRelationships)
                                    <th class="px-5 py-4 font-bold">
                                        <input
                                            type="checkbox"
                                            id="relationship_check_all"
                                            class="h-5 w-5 rounded border-[#A0AEC0]"
                                        >
                                    </th>
                                @endif
                                <th class="px-5 py-4 font-bold">作品</th>
                                <th class="px-5 py-4 font-bold">キャラクター</th>
                                <th class="px-5 py-4 font-bold">相手</th>
                                <th class="px-5 py-4 font-bold">呼び方</th>
                                <th class="px-5 py-4 font-bold">関係性</th>
                                <th class="px-5 py-4 font-bold admin-index-status-head">状態</th>
                                <th class="px-5 py-4 font-bold admin-index-action-head">操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($relationships as $relation)
                                @php
                                    $canModifyRow = (bool) ($relation->can_modify_by_current_user ?? false);

                                    if (! $canModifyRow && $currentUser && method_exists($currentUser, 'canModifyOwnedAdminContent')) {
                                        $canModifyRow = $currentUser->canModifyOwnedAdminContent($relation);
                                    }
                                @endphp

                                <tr class="border-t border-[#E2E8F0]">
                                    <td class="whitespace-nowrap px-3 py-3 text-sm font-semibold text-slate-600"
                                        data-admin-id-value>{{ $relation->id }}</td>
                                    @if ($canManageRelationships)
                                        <td class="px-5 py-4 align-middle">
                                            <input
                                                form="relationship-bulk-form"
                                                type="checkbox"
                                                name="relationship_ids[]"
                                                value="{{ $relation->id }}"
                                                class="relationship-checkbox h-5 w-5 rounded border-[#A0AEC0]"
                                            >
                                        </td>
                                    @endif

                                    <td class="px-5 py-4 align-middle text-[#2D3748]">
                                        {{ $relation->work?->title ?? '未設定' }}
                                    </td>

                                    <td class="px-5 py-4 align-middle text-[#2D3748]">
                                        {{ $relation->fromCharacter?->name ?? '未設定' }}
                                    </td>

                                    <td class="px-5 py-4 align-middle text-[#2D3748]">
                                        {{ $relation->toCharacter?->name ?? '未設定' }}
                                    </td>

                                    <td class="px-5 py-4 align-middle text-[#2D3748]">
                                        {{ $relation->called_name ?: '未設定' }}
                                    </td>

                                    <td class="px-5 py-4 align-middle text-[#2D3748]">
                                        {{ $relation->relationship ?: '未設定' }}
                                    </td>

                                    <td class="px-5 py-4 align-middle admin-index-status-cell">
                                        @include('admin.partials.status-badge', ['status' => $relation->status])
                                    </td>

                                    <td class="px-5 py-4 align-middle admin-index-action-cell">
                                        <div class="flex flex-wrap gap-2">
                                            @if ($canModifyRow)
                                                <a href="{{ route('admin.character-relationships.edit', $relation) }}" class="oshi-btn oshi-btn-sub">
                                                    編集
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.character-relationships.destroy', $relation) }}"
                                                    onsubmit="return confirm('この関係性を削除します。よろしいですか？');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="oshi-btn oshi-btn-sub text-red-600">
                                                        削除
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-sm font-bold text-[#A0AEC0]">
                                                    —
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageRelationships ? 8 : 7 }}" class="px-5 py-8 text-center text-[#718096]">
                                        関係性はまだ登録されていません。
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $relationships->links() }}
            </div>
        </div>
    </div>

    @if ($canManageRelationships)
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

                if (! action) {
                    alert('一括操作の内容を選択してください。');
                    return false;
                }

                if (action === 'delete') {
                    return confirm(checkedCount + '件の関係性に削除フラグを付けます。よろしいですか？');
                }

                return confirm(checkedCount + '件の関係性を一括変更します。よろしいですか？');
            }
        </script>
    @endif
</x-app-layout>
