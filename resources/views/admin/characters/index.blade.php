<x-app-layout>
    @php
$canUseCharacterImports = auth()->user()?->canManageAllAdminFeatures() ?? false;
        $canCreateCharacters = $canUseCharacterImports || auth()->user()?->isStaff();
        $currentAdminUser = auth()->user();
        $currentAdminUserId = $currentAdminUser?->id;
@endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            キャラクター管理
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')
        @include('admin.partials.publish-help')

        <div class="oshi-card admin-index-shell">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4 admin-index-header">
                <div>
                    <h1 class="text-2xl font-bold">
                        キャラクター管理
                    </h1>
                    <p class="oshi-muted">
                        作品ごとのキャラクター情報を管理します。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 admin-index-actions">
                    @if ($canUseCharacterImports)
                        <a href="{{ route('admin.characters.import.create') }}" class="oshi-btn oshi-btn-sub">
                            テキスト取り込み
                        </a>

                        <a href="{{ route('admin.characters.csv-import.create') }}" class="oshi-btn oshi-btn-sub">
                            CSV取り込み
                        </a>

                        <a href="{{ route('admin.characters.csv-export', request()->query()) }}" class="oshi-btn oshi-btn-sub">
                            CSVエクスポート
                        </a>
                    @endif

                    @if ($canCreateCharacters)
                        <a href="{{ route('admin.characters.create') }}" class="oshi-btn">
                            キャラクター登録画面へ
                        </a>
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('admin.characters.index') }}" class="mb-6 admin-index-filter-form">
                <div class="admin-index-filter-grid">
                    <div>
                        <label for="keyword" class="mb-1 block text-sm font-bold text-[#4A5568]">
                            キーワード
                        </label>
                        <input
                            id="keyword"
                            type="text"
                            name="keyword"
                            value="{{ $keyword ?? request('keyword') }}"
                            placeholder="名前・所属・性格など"
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
                            @foreach ($works as $work)
                                <option value="{{ $work->id }}" @selected((string)($selectedWorkId ?? request('work_id')) === (string)$work->id)>
                                    {{ $work->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tag_id" class="mb-1 block text-sm font-bold text-[#4A5568]">
                            タグで絞り込み
                        </label>
                        <select
                            id="tag_id"
                            name="tag_id"
                            class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                        >
                            <option value="">全タグ</option>
                            @foreach (($tags ?? collect()) as $tag)
                                <option value="{{ $tag->id }}" @selected((string)($selectedTagId ?? request('tag_id')) === (string)$tag->id)>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                @include('admin.partials.list-search-extra')


                    <button type="submit" class="oshi-btn">
                        検索・絞り込み
                    </button>

                    <a href="{{ route('admin.characters.index') }}" class="oshi-btn oshi-btn-sub text-center">
                        解除
                    </a>
                </div>
            </form>

            @if ($canUseCharacterImports)
                <form
                    id="character-bulk-form"
                    method="POST"
                    action="{{ route('admin.characters.bulk-action') }}"
                    onsubmit="return confirm('選択したキャラクターに一括操作を実行します。よろしいですか？');"
                    class="mb-6 rounded-3xl bg-[#FFF5F7] p-5"
                >
                    @csrf

                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label for="bulk_action" class="mb-1 block text-sm font-bold text-[#4A5568]">
                                チェックしたキャラクターを一括操作
                            </label>
                            <select
                                id="bulk_action"
                                name="bulk_action"
                                class="rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                                required
                            >
                                <option value="">選択してください</option>
                                <option value="publish">公開</option>
                                <option value="private">非公開</option>
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

            <div class="staff-mobile-table-shell overflow-hidden rounded-3xl border border-[#E2E8F0] bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] table-fixed text-left text-sm">
                        <colgroup>
                            @if ($canUseCharacterImports)
                                <col class="w-[5%]">
                            @endif
                            <col class="{{ $canUseCharacterImports ? 'w-[18%]' : 'w-[20%]' }}">
                            <col class="{{ $canUseCharacterImports ? 'w-[24%]' : 'w-[27%]' }}">
                            <col class="{{ $canUseCharacterImports ? 'w-[16%]' : 'w-[17%]' }}">
                            <col class="w-[11%]">
                            <col class="w-[9%]">
                            <col class="{{ $canUseCharacterImports ? 'w-[17%]' : 'w-[15%]' }}">
                        </colgroup>
                        <thead class="bg-[#FFF5F7] text-[#2D3748]">
                            <tr>
                                @if ($canUseCharacterImports)
                                    <th class="px-5 py-4 font-bold">
                                        <input
                                            type="checkbox"
                                            onclick="document.querySelectorAll('.character-check').forEach(el => el.checked = this.checked)"
                                        >
                                    </th>
                                @endif
                                <th class="px-5 py-4 font-bold whitespace-nowrap">キャラクター名</th>
                                <th class="px-5 py-4 font-bold whitespace-nowrap">作品</th>
                                <th class="px-4 py-4 font-bold whitespace-nowrap">所属</th>
                                <th class="px-4 py-4 font-bold whitespace-nowrap admin-index-status-head">状態</th>
                                <th class="px-4 py-4 font-bold whitespace-nowrap">承認状態</th>
                                <th class="px-4 py-4 text-center font-bold whitespace-nowrap admin-index-action-head">操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($characters as $character)
                                @php
                                    $canModifyCharacter = (bool) ($character->can_modify_by_current_user ?? false);
                                @endphp

                                <tr class="border-t border-[#E2E8F0]">
                                    @if ($canUseCharacterImports)
                                        <td class="px-5 py-4 align-middle">
                                            <input
                                                class="character-check"
                                                form="character-bulk-form"
                                                type="checkbox"
                                                name="character_ids[]"
                                                value="{{ $character->id }}"
                                            >
                                        </td>
                                    @endif

                                    <td class="px-5 py-4 align-middle font-bold leading-7 text-[#2D3748] break-keep">
                                        {{ $character->name }}
                                    </td>

                                    <td class="px-5 py-4 align-middle leading-7 text-[#4A5568] break-keep">
                                        @php
                                            $primaryWork = $character->linkedWorks
                                                ->first(fn ($work) => (bool) $work->pivot?->is_primary)
                                                ?? $character->work;

                                            $additionalWorks = $character->linkedWorks
                                                ->reject(fn ($work) => (int) $work->id === (int) $primaryWork?->id)
                                                ->values();
                                        @endphp

                                        <div class="font-bold text-[#2D3748]">
                                            {{ $primaryWork?->title ?? '—' }}
                                        </div>

                                        @if ($additionalWorks->isNotEmpty())
                                            <details class="mt-1">
                                                <summary class="cursor-pointer text-sm text-[#718096]">
                                                    ほか{{ $additionalWorks->count() }}作品
                                                </summary>
                                                <ul class="mt-2 space-y-1 text-sm text-[#4A5568]">
                                                    @foreach ($additionalWorks as $linkedWork)
                                                        <li>・{{ $linkedWork->title }}</li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 align-middle text-[#4A5568] break-words">
                                        {{ $character->affiliation ?: '—' }}
                                    </td>

                                    <td class="px-4 py-4 align-middle whitespace-nowrap admin-index-status-cell">
                                        @include('admin.partials.status-badge', ['status' => $character->status])
                                    </td>

                                    <td class="px-4 py-4 align-middle whitespace-nowrap text-[#4A5568]">
                                        {{ $character->review_status ?: '—' }}
                                    </td>

                                    <td class="px-3 py-3 align-middle admin-index-action-cell">
                                        <div class="mx-auto inline-flex w-fit flex-col items-stretch justify-center gap-2 whitespace-nowrap">
                                            <a href="{{ route('admin.characters.show', $character) }}" class="oshi-btn oshi-btn-sub min-w-[88px] justify-center px-4 py-2">
                                                詳細
                                            </a>

                                            @if ($canModifyCharacter)
                                                <a href="{{ route('admin.characters.edit', $character) }}" class="oshi-btn oshi-btn-sub min-w-[88px] justify-center px-4 py-2">
                                                    編集
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.characters.destroy', $character) }}"
                                                    class="w-full"
                                                    onsubmit="return confirm('このキャラクターを削除します。よろしいですか？');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="oshi-btn oshi-btn-sub min-w-[88px] justify-center px-4 py-2 text-red-600">
                                                        削除
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canUseCharacterImports ? 7 : 6 }}" class="px-5 py-8 text-center text-[#718096]">
                                        キャラクターが登録されていません。
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @include('admin.characters._staff_mobile_cards')

            <div class="mt-6">
                {{ $characters->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
