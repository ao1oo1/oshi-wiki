<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            @include('admin.partials.flash')

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="oshi-muted">{{ $work->title }}</p>
                    <h1 class="oshi-admin-title">章・編ごとの物語詳細</h1>
                    <p class="oshi-muted">
                        登録数：{{ $sections->count() }} / {{ $limit }}件
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.works.show', $work) }}" class="oshi-btn oshi-btn-sub">
                        作品詳細へ
                    </a>

                    @if (
                        auth()->user()?->canManageAllAdminFeatures()
                        && $sections->count() < $limit
                    )
                        <a href="{{ route('admin.works.story-sections.create', $work) }}" class="oshi-btn">
                            新しい章・編を登録
                        </a>
                    @endif
                </div>
            </div>

            <div class="oshi-card">
                @if ($sections->count() >= $limit)
                    <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                        章・編は1作品につき最大{{ $limit }}件までです。
                    </div>
                @endif

                <div class="oshi-table-wrap">
                    <table class="oshi-table">
                        <thead>
                            <tr>
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
                            @forelse ($sections as $section)
                                <tr>
                                    <td>{{ $section->sort_order }}</td>
                                    <td>{{ $section->typeLabel() }}</td>
                                    <td>
                                        <strong>{{ $section->title }}</strong>
                                        @if ($section->short_label)
                                            <div class="oshi-muted">
                                                {{ $section->short_label }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $section->parentSection?->title ?: '—' }}
                                    </td>
                                    <td>{{ $section->events_count }}件</td>
                                    <td>{{ $section->characters_count }}人</td>
                                    <td>
                                        @include('admin.partials.status-badge', [
                                            'status' => $section->status,
                                        ])
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.works.story-sections.show', [$work, $section]) }}" class="oshi-btn oshi-btn-sub">
                                                詳細
                                            </a>
                                            @if (auth()->user()?->canManageAllAdminFeatures())
                                                <a href="{{ route('admin.works.story-sections.edit', [$work, $section]) }}" class="oshi-btn oshi-btn-sub">
                                                    編集
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        章・編はまだ登録されていません。
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
