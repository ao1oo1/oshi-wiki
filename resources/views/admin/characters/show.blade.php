<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">キャラクター詳細</h2>
    </x-slot>

    @php
        $displayValue = static fn ($value) => filled($value) ? $value : '未設定';
        $sourceTypeLabel = \App\Models\Character::SOURCE_TYPES[$character->source_type] ?? '未設定';
        $sourceReliabilityLabel = \App\Models\Character::SOURCE_RELIABILITIES[$character->source_reliability] ?? '未設定';
        $spoilerLabel = \App\Models\Character::SPOILER_LEVELS[$character->spoiler_level] ?? 'なし';
    @endphp

    <div class="p-6">
        <div class="mx-auto max-w-6xl">
            @include('admin.partials.navigation')

            @if (session('success'))
                <div class="mb-4 rounded-2xl bg-green-100 px-5 py-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex flex-wrap gap-3">
                <a href="{{ route('admin.characters.index') }}" class="oshi-btn oshi-btn-sub">キャラクター一覧へ</a>
                <a href="{{ route('admin.works.show', $character->work) }}" class="oshi-btn oshi-btn-sub">作品詳細へ</a>
                <a href="{{ route('admin.characters.edit', $character) }}" class="oshi-btn">編集する</a>
                <a href="{{ route('admin.character-relationships.create', ['work_id' => $character->work_id]) }}" class="oshi-btn oshi-btn-sub">関係性を追加</a>
            </div>

            @include('admin.partials.publish-help')

            <section class="mb-6 rounded-3xl bg-white p-6 shadow">
                <p class="mb-2 text-sm text-[#718096]">{{ $character->work?->title }}</p>
                <h1 class="text-3xl font-bold text-[#2D3748]">{{ $character->name }}</h1>

                @if ($character->name_kana)
                    <p class="mt-1 text-[#718096]">{{ $character->name_kana }}</p>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    @include('admin.partials.status-badge', ['status' => $character->status])
                    <span class="rounded-full bg-[#FFF5F7] px-3 py-1 text-sm font-bold text-[#2D3748]">
                        ネタバレ：{{ $spoilerLabel }}
                    </span>
                </div>

                @if ($character->tags->count())
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($character->tags as $tag)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="mb-6 rounded-3xl bg-white p-6 shadow">
                <h2 class="mb-5 text-xl font-bold">基本情報</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        '本名' => $character->real_name,
                        '別名・愛称' => $character->aliases,
                        '英語表記' => $character->name_english,
                        '性別' => $character->gender,
                        '年齢' => $character->age,
                        '生年月日・誕生日' => $character->birthday,
                        '身長' => $character->height,
                        '体重' => $character->weight,
                        '血液型' => $character->blood_type,
                        '出身地' => $character->birthplace,
                        '種族' => $character->species,
                        '所属' => $character->affiliation,
                        '学校・学年・クラス' => $character->school_grade_class,
                        '職業・役職' => $character->occupation_position,
                        '家族構成' => $character->family_structure,
                    ] as $label => $value)
                        <div>
                            <h3 class="mb-1 font-bold">{{ $label }}</h3>
                            <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $displayValue($value) }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            @foreach ([
                '外見' => $character->appearance,
                '性格・特徴' => $character->personality,
            ] as $label => $value)
                <section class="mb-6 rounded-3xl bg-white p-6 shadow">
                    <h2 class="mb-3 text-xl font-bold">{{ $label }}</h2>
                    <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $displayValue($value) }}</div>
                </section>
            @endforeach

            <section class="mb-6 rounded-3xl bg-white p-6 shadow">
                <h2 class="mb-5 text-xl font-bold">一人称・口調</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ([
                        '一人称' => $character->first_person,
                        '二人称' => $character->second_person,
                    ] as $label => $value)
                        <div>
                            <h3 class="mb-1 font-bold">{{ $label }}</h3>
                            <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $displayValue($value) }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 space-y-5">
                    @foreach ([
                        '基本口調' => $character->basic_tone,
                        '口癖' => $character->catchphrases,
                        '特徴的な言い回し' => $character->distinctive_speech,
                        '相手による口調の違い' => $character->tone_by_relationship,
                        '短いセリフ例' => $character->short_quote_examples,
                    ] as $label => $value)
                        <div>
                            <h3 class="mb-1 font-bold">{{ $label }}</h3>
                            <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $displayValue($value) }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            @foreach ([
                '能力・技・戦闘' => $character->abilities,
                '背景・経歴' => $character->background,
                '作品内での活躍' => $character->story_activities,
            ] as $label => $value)
                <section class="mb-6 rounded-3xl bg-white p-6 shadow">
                    <h2 class="mb-3 text-xl font-bold">{{ $label }}</h2>
                    <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $displayValue($value) }}</div>
                </section>
            @endforeach

            <section class="mb-6 rounded-3xl bg-white p-6 shadow">
                <h2 class="mb-5 text-xl font-bold">出典</h2>

                <div class="space-y-5">
                    <div>
                        <h3 class="mb-1 font-bold">ページ名または資料名</h3>
                        <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $displayValue($character->source_title) }}</div>
                    </div>

                    <div>
                        <h3 class="mb-1 font-bold">URL</h3>
                        <div class="whitespace-pre-wrap break-all rounded-2xl bg-gray-50 p-4">{{ $displayValue($character->source_url) }}</div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <h3 class="mb-1 font-bold">情報源区分</h3>
                            <div class="rounded-2xl bg-gray-50 p-4">{{ $sourceTypeLabel }}</div>
                        </div>
                        <div>
                            <h3 class="mb-1 font-bold">信頼度</h3>
                            <div class="rounded-2xl bg-gray-50 p-4">{{ $sourceReliabilityLabel }}</div>
                        </div>
                        <div>
                            <h3 class="mb-1 font-bold">確認日</h3>
                            <div class="rounded-2xl bg-gray-50 p-4">{{ $character->source_checked_at?->format('Y年n月j日') ?? '未設定' }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mb-6 rounded-3xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-bold">このキャラクターから見た関係性</h2>

                @if ($character->outgoingRelationships->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr>
                                    <th>相手</th>
                                    <th>呼び方</th>
                                    <th>関係性</th>
                                    <th>印象・気持ち等</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($character->outgoingRelationships as $relation)
                                    <tr>
                                        <td>{{ $relation->toCharacter?->name }}</td>
                                        <td>{{ $relation->called_name ?: '未設定' }}</td>
                                        <td>{{ $relation->relationship ?: '未設定' }}</td>
                                        <td>{{ $relation->impression ?: '未設定' }}</td>
                                        <td>
                                            <a href="{{ route('admin.character-relationships.edit', $relation) }}" class="oshi-btn oshi-btn-sub">編集</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-[#718096]">このキャラクターから見た関係性はまだ登録されていません。</p>
                @endif
            </section>

            <section class="rounded-3xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-bold">他キャラクターから見たこのキャラクター</h2>

                @if ($character->incomingRelationships->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr>
                                    <th>相手</th>
                                    <th>このキャラクターの呼び方</th>
                                    <th>関係性</th>
                                    <th>印象・気持ち等</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($character->incomingRelationships as $relation)
                                    <tr>
                                        <td>{{ $relation->fromCharacter?->name }}</td>
                                        <td>{{ $relation->called_name ?: '未設定' }}</td>
                                        <td>{{ $relation->relationship ?: '未設定' }}</td>
                                        <td>{{ $relation->impression ?: '未設定' }}</td>
                                        <td>
                                            <a href="{{ route('admin.character-relationships.edit', $relation) }}" class="oshi-btn oshi-btn-sub">編集</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-[#718096]">他キャラクターから見た関係性はまだ登録されていません。</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
