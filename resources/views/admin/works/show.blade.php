<x-app-layout>
    @php
        $canManageWorks = auth()->user()?->canManageAllAdminFeatures() ?? false;
        $eventStatusLabels = [
            'occurred' => 'すでに起きた',
            'allowed' => '触れてよい',
            'not_yet' => 'まだ起きていない',
            'unknown' => '時期不明',
        ];
        $detailCategories = [
            '物語の設計' => [
                ['timeline_setting', '時間軸の指定'],
            ],
            '建物・空間' => [
                ['building_layout', '校舎や寮の間取り・構造'],
                ['character_room_seat', 'キャラごとの部屋・席の位置'],
                ['hangout_places', 'キャラがよくいる場所・たまり場'],
                ['restricted_secret_places', '立ち入り禁止区域・秘密の場所'],
                ['cafeteria_store_menu', '食堂・購買のメニューや人気商品'],
            ],
            '生活・ルール' => [
                ['daily_schedule', '一日のスケジュール'],
                ['school_dorm_rules', '校則・寮則'],
                ['uniform_details', '制服の詳細'],
                ['casual_holiday_rules', '私服・休日の過ごし方のルール'],
                ['duty_system', '当番制度'],
            ],
            '組織・制度' => [
                ['class_grade_system', 'クラス編成・学年の仕組み'],
                ['organizations_memberships', '生徒会・委員会・部活動とキャラの所属'],
                ['ranking_system', '成績・序列の制度'],
                ['adult_roles', '教師・寮母など大人キャラの配置と役割'],
            ],
            '行事・時間の流れ' => [
                ['annual_events', '年間行事とその時期'],
                ['event_flow', '行事の具体的な流れ・名物イベント'],
                ['story_season', '作中の季節・月がわかる情報'],
            ],
            '地理・周辺環境' => [
                ['school_location', '学園の所在地'],
                ['commute_environment', '通学手段・通学路の風景'],
                ['nearby_shops', '近くの店・生徒の行きつけ'],
                ['climate_nature', '気候・自然環境'],
            ],
            '小物・感覚的な情報' => [
                ['sounds', '音'],
                ['symbolic_motifs', '学園の象徴的なモチーフ'],
                ['required_belongings', '持ち物の指定'],
            ],
        ];
    @endphp

    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            @include('admin.partials.flash')

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="oshi-muted">作品ID：{{ $work->id }}</p>
                    <h1 class="oshi-admin-title">{{ $work->title }}</h1>
                    @if ($work->title_kana)<p class="oshi-muted">{{ $work->title_kana }}</p>@endif
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">作品一覧へ</a>
                    @if ($canManageWorks)
                        <a href="{{ route('admin.works.edit', $work) }}" class="oshi-btn oshi-btn-main">作品編集</a>
                    @endif
                    <a href="{{ route('admin.characters.create', ['work_id' => $work->id]) }}" class="oshi-btn oshi-btn-sub">キャラクター追加</a>
                    <a href="{{ route('admin.character-relationships.create', ['work_id' => $work->id]) }}" class="oshi-btn oshi-btn-sub">関係性追加</a>
                </div>
            </div>

            <div class="oshi-card mb-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div><p class="oshi-label">ジャンル</p><p>{{ $work->genre ?: '未設定' }}</p></div>
                    <div><p class="oshi-label">原作媒体</p><p>{{ $work->original_media ?: '未設定' }}</p></div>
                    <div><p class="oshi-label">状態</p>@include('admin.partials.status-badge', ['status' => $work->status])</div>
                </div>
                <div class="mt-5">
                    <p class="oshi-label">説明</p>
                    <div class="whitespace-pre-wrap rounded-xl bg-gray-50 p-4">{{ $work->description ?: '未設定' }}</div>
                </div>
                <div class="mt-5">
                    <p class="oshi-label">作品タグ</p>
                    @forelse ($work->tags as $tag)
                        <span class="oshi-chip">{{ $tag->name }}</span>
                    @empty
                        <span class="oshi-muted">未設定</span>
                    @endforelse
                </div>
            </div>

            @foreach ($detailCategories as $category => $fields)
                @php
                    $hasValue = collect($fields)->contains(fn ($field) => filled($work->{$field[0]}));
                @endphp
                <details class="oshi-card mb-5" @if ($category === '物語の設計') open @endif>
                    <summary class="cursor-pointer text-lg font-bold">{{ $category }}</summary>
                    <div class="mt-5 space-y-5">
                        @foreach ($fields as [$name, $label])
                            <div>
                                <p class="oshi-label">{{ $label }}</p>
                                <div class="whitespace-pre-wrap rounded-xl bg-gray-50 p-4">{{ $work->{$name} ?: '未設定' }}</div>
                            </div>
                        @endforeach

                        @if ($category === '物語の設計')
                            <div>
                                <p class="oshi-label">原作の重要イベント年表</p>
                                @forelse ($work->canonEvents as $event)
                                    <div class="mb-3 rounded-xl border border-gray-200 p-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <strong>{{ $event->event_name }}</strong>
                                            @if ($event->timing)<span class="oshi-chip">{{ $event->timing }}</span>@endif
                                            @if ($event->event_status)<span class="oshi-chip">{{ $eventStatusLabels[$event->event_status] ?? $event->event_status }}</span>@endif
                                        </div>
                                        @if ($event->notes)<p class="mt-2 whitespace-pre-wrap">{{ $event->notes }}</p>@endif
                                    </div>
                                @empty
                                    <p class="oshi-muted">未設定</p>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </details>
            @endforeach

            <details class="oshi-card mb-5">
                <summary class="cursor-pointer text-lg font-bold">用語</summary>
                <div class="mt-5">
                    @forelse ($work->termUsages as $term)
                        <div class="mb-4 rounded-xl border border-gray-200 p-4">
                            <h3 class="font-bold">{{ $term->term }}</h3>
                            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div><p class="oshi-label">意味</p><p class="whitespace-pre-wrap">{{ $term->meaning ?: '未設定' }}</p></div>
                                <div><p class="oshi-label">作中での使用例</p><p class="whitespace-pre-wrap">{{ $term->usage_example ?: '未設定' }}</p></div>
                            </div>
                        </div>
                    @empty
                        <p class="oshi-muted">未設定</p>
                    @endforelse
                </div>
            </details>

            <div class="oshi-card mb-5">
                <h2 class="mb-4 text-lg font-bold">登録キャラクター</h2>
                @if ($work->characters->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead><tr><th>名前</th><th>年齢</th><th>所属</th><th>一人称</th><th>操作</th></tr></thead>
                            <tbody>
                            @foreach ($work->characters as $character)
                                <tr>
                                    <td>{{ $character->name }}</td>
                                    <td>{{ $character->age ?: '未設定' }}</td>
                                    <td>{{ $character->affiliation ?: '未設定' }}</td>
                                    <td>{{ $character->first_person ?: '未設定' }}</td>
                                    <td><a href="{{ route('admin.characters.show', $character) }}" class="oshi-btn oshi-btn-sub">詳細</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="oshi-muted">この作品にはまだキャラクターが登録されていません。</p>
                @endif
            </div>

            <div class="oshi-card">
                <h2 class="mb-4 text-lg font-bold">キャラクター関係性</h2>
                @if ($work->characterRelationships->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead><tr><th>キャラクター</th><th>相手</th><th>呼ばれ方</th><th>関係性</th><th>印象・気持ち等</th></tr></thead>
                            <tbody>
                            @foreach ($work->characterRelationships as $relation)
                                <tr>
                                    <td>{{ $relation->fromCharacter?->name }}</td>
                                    <td>{{ $relation->toCharacter?->name }}</td>
                                    <td>{{ $relation->called_name ?: '未設定' }}</td>
                                    <td>{{ $relation->relationship ?: '未設定' }}</td>
                                    <td>{{ $relation->impression ?: '未設定' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="oshi-muted">この作品にはまだ関係性が登録されていません。</p>
                @endif
            </div>

            @if ($canManageWorks)
                <form method="POST" action="{{ route('admin.works.destroy', $work) }}" class="mt-6"
                    onsubmit="return confirm('この作品を削除しますか？紐づくキャラクター・関係性も削除されます。');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="oshi-btn bg-red-600 text-white">この作品を削除</button>
                </form>
            @endif
        </main>
    </div>
</x-app-layout>
