<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            キャラクター重複チェック
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">
                        キャラクター重複チェック
                    </h1>
                    <p class="mt-2 text-sm text-[#718096]">
                        name と work_id が完全一致するデータを表示します。
                        各グループでは最も若いIDを残し、それ以外を統合後に削除します。
                    </p>
                </div>

                <a
                    href="{{ route('admin.characters.index') }}"
                    class="oshi-btn oshi-btn-sub"
                >
                    キャラクター管理へ戻る
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
                        削除対象キャラクター
                    </div>
                    <div class="mt-1 text-3xl font-black text-[#2D3748]">
                        {{ number_format($duplicateCharacterCount) }}
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm leading-7 text-amber-900">
                <p class="font-bold">統合時に移行する関連情報</p>
                <p>
                    タグ、複数作品への紐付け、章・編の登場人物、
                    公式キャラクター関係性、Writer側の関係性、
                    保存プロンプトの選択キャラクターを保持IDへ移します。
                </p>
                <p class="mt-2">
                    重複側は完全削除ではなく、ゴミ箱へ移動します。
                </p>
            </div>

            @if ($duplicateGroups->isEmpty())
                <div class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-10 text-center">
                    <p class="text-lg font-bold text-[#2D3748]">
                        重複キャラクターはありません。
                    </p>
                </div>
            @else
                <form
                    method="POST"
                    action="{{ route('admin.characters.duplicates.merge') }}"
                    class="mt-8"
                    onsubmit="return confirmCharacterDuplicateMerge(this);"
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
                            選択グループを一括統合
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
                                                {{ $group['name'] }}
                                            </span>
                                            <span class="block text-sm text-[#718096]">
                                                {{ $group['work_title'] }}
                                                ／ work_id: {{ $group['work_id'] }}
                                            </span>
                                        </span>
                                    </label>

                                    <span class="rounded-full bg-red-50 px-4 py-2 text-sm font-bold text-red-700">
                                        削除対象 {{ $group['duplicate_count'] }}件
                                    </span>
                                </div>

                                <div class="mt-5 overflow-x-auto">
                                    <table class="w-full min-w-[760px] text-left text-sm">
                                        <thead class="bg-[#FFF5F7]">
                                            <tr>
                                                <th class="px-4 py-3">扱い</th>
                                                <th class="px-4 py-3">ID</th>
                                                <th class="px-4 py-3">名前</th>
                                                <th class="px-4 py-3">所属</th>
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
                                                                統合後削除
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 font-bold">
                                                        {{ $row->id }}
                                                    </td>
                                                    <td class="px-4 py-3 font-bold">
                                                        {{ $row->name }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $row->affiliation ?: '—' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $row->status ?: '—' }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        {{ optional($row->created_at)->format('Y-m-d H:i') ?: '—' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <a
                                                            href="{{ route('admin.characters.show', $row) }}"
                                                            class="oshi-btn oshi-btn-sub px-3 py-2"
                                                            target="_blank"
                                                            rel="noopener"
                                                        >
                                                            詳細
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

        function confirmCharacterDuplicateMerge(form) {
            const checks = Array.from(
                form.querySelectorAll(
                    '.duplicate-group-check:checked'
                )
            );

            if (checks.length === 0) {
                alert(
                    '統合する重複グループを選択してください。'
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
                `${checks.length}グループを統合し、`
                + `${deleteCount}件の重複キャラクターを`
                + 'ゴミ箱へ移動します。\n'
                + '各グループでは最小IDが残ります。\n'
                + '実行してよろしいですか？'
            );
        }
    </script>
</x-app-layout>
