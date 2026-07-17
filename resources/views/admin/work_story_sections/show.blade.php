<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            @include('admin.partials.flash')

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="oshi-muted">{{ $work->title }}</p>
                    <h1 class="oshi-admin-title">{{ $section->title }}</h1>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.works.story-sections.index', $work) }}" class="oshi-btn oshi-btn-sub">
                        章・編一覧へ
                    </a>
                    @if (auth()->user()?->canManageAllAdminFeatures())
                        <a href="{{ route('admin.works.story-sections.edit', [$work, $section]) }}" class="oshi-btn">
                            編集
                        </a>
                    @endif
                </div>
            </div>

            <div class="oshi-card mb-5">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <p class="oshi-label">種別</p>
                        <p>{{ $section->typeLabel() }}</p>
                    </div>
                    <div>
                        <p class="oshi-label">親の編・部</p>
                        <p>{{ $section->parentSection?->title ?: 'なし' }}</p>
                    </div>
                    <div>
                        <p class="oshi-label">状態</p>
                        @include('admin.partials.status-badge', [
                            'status' => $section->status,
                        ])
                    </div>
                </div>

                <div class="mt-5">
                    <p class="oshi-label">概要</p>
                    <div class="whitespace-pre-wrap rounded-xl bg-gray-50 p-4">
                        {{ $section->synopsis ?: '未設定' }}
                    </div>
                </div>

                <div class="mt-5">
                    <p class="oshi-label">この章までに登場する設定</p>
                    <div class="whitespace-pre-wrap rounded-xl bg-gray-50 p-4">
                        {{ $section->cumulative_settings ?: '未設定' }}
                    </div>
                </div>

                <div class="mt-5">
                    <p class="oshi-label">備考</p>
                    <div class="whitespace-pre-wrap rounded-xl bg-gray-50 p-4">
                        {{ $section->notes ?: '未設定' }}
                    </div>
                </div>
            </div>

            <div class="oshi-card mb-5">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">
                            物語詳細（{{ $section->events->count() }}件）
                        </h2>
                        <p class="mt-1 text-sm text-[#718096]">
                            この章で起きる出来事を時系列で管理します。
                        </p>
                    </div>

                    @if (auth()->user()?->canManageAllAdminFeatures())
                        <div class="flex flex-wrap gap-2">
                            <a
                                href="{{ route(
                                    'admin.works.story-sections.events.csv.create',
                                    [$work, $section]
                                ) }}"
                                class="oshi-btn"
                            >
                                CSVで追加・更新
                            </a>

                            <a
                                href="{{ route(
                                    'admin.works.story-sections.events.csv.export',
                                    [$work, $section]
                                ) }}"
                                class="oshi-btn oshi-btn-sub"
                            >
                                この章をCSV出力
                            </a>
                        </div>
                    @endif
                </div>

                @forelse ($section->events as $event)
                    <div class="mb-4 rounded-xl border border-gray-200 p-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <strong>{{ $event->title }}</strong>
                            @if ($event->timing)
                                <span class="oshi-chip">{{ $event->timing }}</span>
                            @endif
                            @if ($event->location)
                                <span class="oshi-chip">{{ $event->location }}</span>
                            @endif
                        </div>

                        @if ($event->summary)
                            <p class="mt-3 whitespace-pre-wrap">{{ $event->summary }}</p>
                        @endif
                        @if ($event->outcome)
                            <div class="mt-3 rounded-lg bg-gray-50 p-3">
                                <strong>結果</strong>
                                <p class="whitespace-pre-wrap">{{ $event->outcome }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="oshi-muted">未設定</p>
                @endforelse
            </div>

            <div class="oshi-card">
                <h2 class="mb-4 text-lg font-bold">
                    登場キャラクター（{{ $section->characters->count() }}人）
                </h2>

                <div class="oshi-table-wrap">
                    <table class="oshi-table">
                        <thead>
                            <tr>
                                <th>キャラクター</th>
                                <th>登場区分</th>
                                <th>年齢</th>
                                <th>学年・クラス</th>
                                <th>所属・役職</th>
                                <th>備考</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($section->characters as $character)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.characters.show', $character) }}">
                                            {{ $character->name }}
                                        </a>
                                    </td>
                                    <td>{{ $character->pivot->appearance_type }}</td>
                                    <td>{{ $character->pivot->age_at_section ?: '未設定' }}</td>
                                    <td>
                                        {{
                                            collect([
                                                $character->pivot->school_grade_at_section,
                                                $character->pivot->class_at_section,
                                            ])->filter()->implode(' / ')
                                            ?: '未設定'
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            collect([
                                                $character->pivot->affiliation_at_section,
                                                $character->pivot->position_at_section,
                                            ])->filter()->implode(' / ')
                                            ?: '未設定'
                                        }}
                                    </td>
                                    <td class="whitespace-pre-wrap">
                                        {{ $character->pivot->notes ?: '未設定' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">未設定</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (auth()->user()?->canManageAllAdminFeatures())
                <form method="POST" action="{{ route('admin.works.story-sections.destroy', [$work, $section]) }}" class="mt-6" onsubmit="return confirm('この章・編を削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button class="oshi-btn bg-red-600 text-white" type="submit">
                        この章・編を削除
                    </button>
                </form>
            @endif
        </main>
    </div>
</x-app-layout>
