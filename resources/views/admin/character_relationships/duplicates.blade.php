<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            関係性重複チェック
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">
                        関係性重複チェック
                    </h1>
                    <p class="mt-2 text-sm text-[#718096]">
                        work_id、from_character_id、
                        to_character_idが完全一致するデータを表示します。
                        各グループでは最も若いrelationship_idを残します。
                    </p>
                </div>

                <a
                    href="{{ route('admin.character-relationships.index') }}"
                    class="oshi-btn oshi-btn-sub"
                >
                    関係性管理へ戻る
                </a>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-3xl bg-[#FFF5F7] p-5">
                    <div class="text-sm font-bold text-[#718096]">
                        重複グループ
                    </div>
                    <div class="mt-1 text-3xl font-black text-[#2D3748]">
                        {{ number_format($duplicateGroupCount) }}
                    </div>
                </div>

                <div class="rounded-3xl bg-[#FFF5F7] p-5">
                    <div class="text-sm font-bold text-[#718096]">
                        削除対象の関係性
                    </div>
                    <div class="mt-1 text-3xl font-black text-[#2D3748]">
                        {{ number_format($duplicateRelationshipCount) }}
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm leading-7 text-amber-900">
                <p class="font-bold">処理内容</p>
                <p>
                    各グループの最小relationship_idを残し、
                    それ以外には削除フラグを付けてゴミ箱へ移動します。
                    残すデータの内容は変更しません。
                </p>
            </div>

            @if ($duplicateGroups->isEmpty())
                <div class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-10 text-center">
                    <p class="text-lg font-bold text-[#2D3748]">
                        重複関係性はありません。
                    </p>
                </div>
            @else
                <form
                    method="POST"
                    action="{{ route('admin.character-relationships.duplicates.merge') }}"
                    class="mt-8"
                    onsubmit="return confirmRelationshipDuplicateMerge(this);"
                >
                    @csrf

                    <div class="mb-5 flex flex-wrap items-center gap-3 rounded-3xl bg-[#FFF5F7] p-5">
                        <label class="inline-flex cursor-pointer items-center gap-2 font-bold">
                            <input
                                id="duplicate-select-all"
                                type="checkbox"
                                checked
                            >
                            全グループを選択
                        </label>

                        <button
                            type="submit"
                            class="oshi-btn"
                        >
                            選択グループを一括整理
                        </button>

                        <span
                            id="duplicate-selected-summary"
                            class="text-sm font-bold text-[#718096]"
                        ></span>
                    </div>

                    <div class="space-y-5">
                        @foreach ($duplicateGroups as $group)
                            <section
                                class="duplicate-group rounded-3xl border border-[#E2E8F0] bg-white p-5"
                                data-delete-count="{{ $group['duplicate_count'] }}"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <label class="flex cursor-pointer items-start gap-3">
                                        <input
                                            class="duplicate-group-check mt-1"
                                            type="checkbox"
                                            name="duplicate_groups[]"
                                            value="{{ $group['token'] }}"
                                            checked
                                        >

                                        <span>
                                            <span class="block text-lg font-black text-[#2D3748]">
                                                {{ $group['from_character_name'] }}
                                                →
                                                {{ $group['to_character_name'] }}
                                            </span>
                                            <span class="block text-sm text-[#718096]">
                                                {{ $group['work_title'] }}
                                                ／ work_id: {{ $group['work_id'] }}
                                                ／ from: {{ $group['from_character_id'] }}
                                                ／ to: {{ $group['to_character_id'] }}
                                            </span>
                                        </span>
                                    </label>

                                    <span class="rounded-full bg-red-50 px-4 py-2 text-sm font-bold text-red-700">
                                        削除対象 {{ $group['duplicate_count'] }}件
                                    </span>
                                </div>

                                <div class="mt-5 overflow-x-auto">
                                    <table class="w-full min-w-[1100px] text-left text-sm">
                                        <thead class="bg-[#FFF5F7]">
                                            <tr>
                                                <th class="px-4 py-3">扱い</th>
                                                <th class="px-4 py-3">ID</th>
                                                <th class="px-4 py-3">呼び方</th>
                                                <th class="px-4 py-3">関係性</th>
                                                <th class="px-4 py-3">印象</th>
                                                <th class="px-4 py-3">備考</th>
                                                <th class="px-4 py-3">状態</th>
                                                <th class="px-4 py-3">登録日時</th>
                                                <th class="px-4 py-3">確認</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $rows = collect([$group['keep']])
                                                    ->concat($group['duplicates']);
                                            @endphp

                                            @foreach ($rows as $row)
                                                @php
                                                    $isKeep = (int) $row->id
                                                        === (int) $group['keep']->id;
                                                @endphp

                                                <tr class="border-t border-[#E2E8F0]">
                                                    <td class="px-4 py-3">
                                                        @if ($isKeep)
                                                            <span class="rounded-full bg-green-50 px-3 py-1 font-bold text-green-700">
                                                                残す
                                                            </span>
                                                        @else
                                                            <span class="rounded-full bg-red-50 px-3 py-1 font-bold text-red-700">
                                                                削除
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 font-bold">
                                                        {{ $row->id }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $row->called_name ?: '—' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $row->relationship ?: '—' }}
                                                    </td>
                                                    <td class="max-w-[260px] px-4 py-3">
                                                        <div class="line-clamp-4 whitespace-pre-wrap">
                                                            {{ $row->impression ?: '—' }}
                                                        </div>
                                                    </td>
                                                    <td class="max-w-[260px] px-4 py-3">
                                                        <div class="line-clamp-4 whitespace-pre-wrap">
                                                            {{ $row->notes ?: '—' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $row->status ?: '—' }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        {{ optional($row->created_at)->format('Y-m-d H:i') ?: '—' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <a
                                                            href="{{ route('admin.character-relationships.edit', $row) }}"
                                                            class="oshi-btn oshi-btn-sub px-3 py-2"
                                                            target="_blank"
                                                            rel="noopener"
                                                        >
                                                            編集
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endforeach
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectAll = document.getElementById(
                'duplicate-select-all'
            );
            const checks = Array.from(
                document.querySelectorAll(
                    '.duplicate-group-check'
                )
            );
            const summary = document.getElementById(
                'duplicate-selected-summary'
            );

            const updateSummary = () => {
                const selected = checks.filter(
                    (check) => check.checked
                );
                const deleteCount = selected.reduce(
                    (total, check) => {
                        const group = check.closest(
                            '.duplicate-group'
                        );

                        return total + Number(
                            group?.dataset.deleteCount || 0
                        );
                    },
                    0
                );

                if (summary) {
                    summary.textContent =
                        `${selected.length}グループ／`
                        + `${deleteCount}件を削除対象として選択中`;
                }

                if (selectAll) {
                    selectAll.checked =
                        checks.length > 0
                        && selected.length === checks.length;
                    selectAll.indeterminate =
                        selected.length > 0
                        && selected.length < checks.length;
                }
            };

            selectAll?.addEventListener(
                'change',
                () => {
                    checks.forEach((check) => {
                        check.checked = selectAll.checked;
                    });

                    updateSummary();
                }
            );

            checks.forEach((check) => {
                check.addEventListener(
                    'change',
                    updateSummary
                );
            });

            updateSummary();
        });

        function confirmRelationshipDuplicateMerge(form) {
            const checks = Array.from(
                form.querySelectorAll(
                    '.duplicate-group-check:checked'
                )
            );

            if (checks.length === 0) {
                alert(
                    '整理する重複グループを選択してください。'
                );

                return false;
            }

            const deleteCount = checks.reduce(
                (total, check) => {
                    const group = check.closest(
                        '.duplicate-group'
                    );

                    return total + Number(
                        group?.dataset.deleteCount || 0
                    );
                },
                0
            );

            return confirm(
                `${checks.length}グループを整理し、`
                + `${deleteCount}件の重複関係性を`
                + 'ゴミ箱へ移動します。\n'
                + '各グループでは最小IDが残ります。\n'
                + '実行してよろしいですか？'
            );
        }
    </script>
</x-app-layout>
