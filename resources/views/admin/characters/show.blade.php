<x-app-layout>
    @php
        $canUseCharacterImports = auth()->user()?->canManageAllAdminFeatures() ?? false;
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            キャラクター詳細
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="mx-auto max-w-5xl">
            @include('admin.partials.navigation')


            @if (session('success'))
                <div class="mb-4 rounded bg-green-100 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex flex-wrap gap-3">
                <a
                    href="{{ route('admin.characters.index') }}"
                    style="display:inline-block;background:#4b5563;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    キャラクター一覧へ
                </a>

                <a
                    href="{{ route('admin.works.show', $character->work) }}"
                    style="display:inline-block;background:#16a34a;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    作品詳細へ
                </a>

                <a
                    href="{{ route('admin.characters.edit', $character) }}"
                    style="display:inline-block;background:#2563eb;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    編集する
                </a>

                <a
                    href="{{ route('admin.character-relationships.create', ['work_id' => $character->work_id]) }}"
                    style="display:inline-block;background:#9333ea;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    関係性を追加
                </a>
            </div>

            @include('admin.partials.publish-help')

            <div class="mb-6 rounded bg-white p-6 shadow">
                <div class="mb-6 border-b pb-4">
                    <p class="mb-2 text-sm text-gray-500">
                        {{ $character->work?->title }}
                    </p>

                    <h3 class="text-2xl font-bold">
                        {{ $character->name }}
                    </h3>

                    @if ($character->name_kana)
                        <p class="mt-1 text-gray-600">
                            {{ $character->name_kana }}
                        </p>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <h4 class="mb-1 font-semibold">年齢</h4>
                        <p class="rounded bg-gray-50 p-3">
                            {{ $character->age ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">一人称</h4>
                        <p class="rounded bg-gray-50 p-3">
                            {{ $character->first_person ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">所属</h4>
                        <p class="rounded bg-gray-50 p-3">
                            {{ $character->affiliation ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">学年クラス</h4>
                        <p class="rounded bg-gray-50 p-3">
                            {{ $character->grade_class ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">状態</h4>
                        <div class="rounded bg-gray-50 p-3">
                            @include('admin.partials.status-badge', ['status' => $character->status])
                        </div>
                    </div>
                </div>


                <div class="mt-6">
                    <h4 class="mb-2 font-semibold">キャラクタータグ</h4>

                    @if ($character->tags->count())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($character->tags as $tag)
                                <span class="rounded bg-gray-100 px-3 py-1 text-sm">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">
                            タグは未設定です。
                        </p>
                    @endif
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <h4 class="mb-1 font-semibold">口調</h4>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->tone ?: '未設定' }}</div>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">口調の例</h4>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->tone_examples ?: '未設定' }}</div>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">性格</h4>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->personality ?: '未設定' }}</div>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">外見の特徴</h4>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->appearance ?: '未設定' }}</div>
                    </div>

                    <div>
                        <h4 class="mb-1 font-semibold">背景・経歴</h4>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->background ?: '未設定' }}</div>
                    </div>
                </div>
            </div>

            <div class="mb-6 rounded bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold">
                    このキャラクターから見た関係性
                </h3>

                @if ($character->outgoingRelationships->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="px-4 py-2 text-left">相手</th>
                                    <th class="px-4 py-2 text-left">呼び方</th>
                                    <th class="px-4 py-2 text-left">関係性</th>
                                    <th class="px-4 py-2 text-left">印象・気持ち等</th>
                                    <th class="px-4 py-2 text-left">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($character->outgoingRelationships as $relation)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">
                                            {{ $relation->toCharacter?->name }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->called_name ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->relationship ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->impression ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <a
                                                href="{{ route('admin.character-relationships.edit', $relation) }}"
                                                style="display:inline-block;background:#2563eb;color:#ffffff;padding:6px 12px;border-radius:6px;text-decoration:none;"
                                            >
                                                編集
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-600">
                        このキャラクターから見た関係性はまだ登録されていません。
                    </p>
                @endif
            </div>

            <div class="rounded bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold">
                    他キャラクターから見たこのキャラクター
                </h3>

                @if ($character->incomingRelationships->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="px-4 py-2 text-left">相手</th>
                                    <th class="px-4 py-2 text-left">このキャラクターの呼び方</th>
                                    <th class="px-4 py-2 text-left">関係性</th>
                                    <th class="px-4 py-2 text-left">印象・気持ち等</th>
                                    <th class="px-4 py-2 text-left">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($character->incomingRelationships as $relation)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">
                                            {{ $relation->fromCharacter?->name }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->called_name ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->relationship ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $relation->impression ?: '未設定' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <a
                                                href="{{ route('admin.character-relationships.edit', $relation) }}"
                                                style="display:inline-block;background:#2563eb;color:#ffffff;padding:6px 12px;border-radius:6px;text-decoration:none;"
                                            >
                                                編集
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-600">
                        他キャラクターから見た関係性はまだ登録されていません。
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
