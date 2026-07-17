<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            @include('admin.partials.flash')

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="oshi-muted">{{ $work->title }}</p>
                    <h1 class="oshi-admin-title">
                        章・編ごとの物語詳細
                    </h1>
                    <p class="oshi-muted">
                        登録数：{{ $sections->count() }} / {{ $limit }}件
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('admin.works.show', $work) }}"
                        class="oshi-btn oshi-btn-sub"
                    >
                        作品詳細へ
                    </a>

                    @if (auth()->user()?->canManageAllAdminFeatures())
                        <a
                            href="{{ route(
                                'admin.works.story-sections.text-import.create',
                                $work
                            ) }}"
                            class="oshi-btn oshi-btn-sub"
                        >
                            テキスト取り込み
                        </a>

                        <a
                            href="{{ route(
                                'admin.works.story-sections.csv.create',
                                $work
                            ) }}"
                            class="oshi-btn oshi-btn-sub"
                        >
                            CSV取り込み・出力
                        </a>
                    @endif

                    @if (
                        auth()->user()?->canManageAllAdminFeatures()
                        && $sections->count() < $limit
                    )
                        <a
                            href="{{ route(
                                'admin.works.story-sections.create',
                                $work
                            ) }}"
                            class="oshi-btn"
                        >
                            新しい章・編を登録
                        </a>
                    @endif
                </div>
            </div>

            @if ($sections->count() >= $limit)
                <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                    章・編は1作品につき最大{{ $limit }}件までです。
                </div>
            @endif

            @if (
                auth()->user()?->canManageAllAdminFeatures()
                && $sections->isNotEmpty()
            )
                <form
                    method="POST"
                    action="{{ route(
                        'admin.works.story-sections.bulk-action',
                        $work
                    ) }}"
                    onsubmit="return confirmStorySectionBulkAction();"
                >
                    @csrf

                    <div class="mb-5 flex flex-wrap items-end gap-3 rounded-xl bg-pink-50 p-4">
                        <div>
                            <label for="story_section_bulk_action">
                                チェックした章・編を一括操作
                            </label>

                            <select
                                id="story_section_bulk_action"
                                name="bulk_action"
                            >
                                <option value="">
                                    選択してください
                                </option>
                                <option value="publish">
                                    公開にする
                                </option>
                                <option value="private">
                                    非公開にする
                                </option>
                                <option value="draft">
                                    下書きに戻す
                                </option>
                                <option value="delete">
                                    削除フラグを付ける
                                </option>
                            </select>
                        </div>

                        <button class="oshi-btn" type="submit">
                            一括反映
                        </button>

                        <p class="text-sm text-gray-600">
                            削除は完全削除ではなく、削除フラグを付ける処理です。
                        </p>
                    </div>

                    <div class="oshi-card">
                        <div class="oshi-table-wrap">
                            <table class="oshi-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <input
                                                type="checkbox"
                                                id="story_section_check_all"
                                            >
                                        </th>
                                        <th>順番</th>
                                        <th>種別</th>
                                        <th>章・編名</th>
                                        <th>親の編・部</th>
                                        <th>物語詳細</th>
                                        <th>登場キャラ</th>
                                        <th>状態</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sections as $section)
                                        <tr>
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    name="section_ids[]"
                                                    value="{{ $section->id }}"
                                                    class="story-section-checkbox"
                                                >
                                            </td>
                                            <td>{{ $section->sort_order }}</td>
                                            <td>{{ $section->typeLabel() }}</td>
                                            <td>
                                                <strong>
                                                    {{ $section->title }}
                                                </strong>

                                                @if ($section->short_label)
                                                    <div class="oshi-muted">
                                                        {{ $section->short_label }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $section->parentSection?->title ?: '—' }}
                                            </td>
                                            <td>
                                                {{ $section->events_count }}件
                                            </td>
                                            <td>
                                                {{ $section->characters_count }}人
                                            </td>
                                            <td>
                                                @include(
                                                    'admin.partials.status-badge',
                                                    [
                                                        'status' =>
                                                            $section->status,
                                                    ]
                                                )
                                            </td>
                                            <td>
                                                <div class="flex flex-wrap gap-2">
                                                    <a
                                                        href="{{ route(
                                                            'admin.works.story-sections.show',
                                                            [$work, $section]
                                                        ) }}"
                                                        class="oshi-btn oshi-btn-sub"
                                                    >
                                                        詳細
                                                    </a>

                                                    <a
                                                        href="{{ route(
                                                            'admin.works.story-sections.edit',
                                                            [$work, $section]
                                                        ) }}"
                                                        class="oshi-btn oshi-btn-sub"
                                                    >
                                                        編集
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            @else
                <div class="oshi-card">
                    <div class="oshi-empty">
                        章・編はまだ登録されていません。
                    </div>
                </div>
            @endif
        </main>
    </div>

    <script>
        const storySectionCheckAll = document.getElementById(
            'story_section_check_all'
        );

        storySectionCheckAll?.addEventListener(
            'change',
            function () {
                document
                    .querySelectorAll('.story-section-checkbox')
                    .forEach(function (checkbox) {
                        checkbox.checked =
                            storySectionCheckAll.checked;
                    });
            }
        );

        function confirmStorySectionBulkAction() {
            const count = document.querySelectorAll(
                '.story-section-checkbox:checked'
            ).length;

            const action = document.getElementById(
                'story_section_bulk_action'
            )?.value;

            if (count === 0) {
                alert(
                    '一括操作する章・編を選択してください。'
                );

                return false;
            }

            if (! action) {
                alert(
                    '一括操作の内容を選択してください。'
                );

                return false;
            }

            if (action === 'delete') {
                return confirm(
                    count
                    + '件の章・編に削除フラグを付けます。'
                    + '子章を持つ編・部は削除できません。'
                    + 'よろしいですか？'
                );
            }

            return confirm(
                count
                + '件の章・編を一括変更します。'
                + 'よろしいですか？'
            );
        }
    </script>
</x-app-layout>
