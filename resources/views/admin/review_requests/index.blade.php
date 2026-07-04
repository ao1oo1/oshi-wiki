<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            承認待ち一覧
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <h1 class="mb-2 text-2xl font-bold">
                承認待ち一覧
            </h1>

            <p class="oshi-muted mb-6">
                情報入力スタッフが登録・編集したデータを、最高管理者が最終確認して公開します。
            </p>

            @php
                $groups = [
                    'works' => ['label' => '作品', 'items' => $works],
                    'characters' => ['label' => 'キャラクター', 'items' => $characters],
                    'relationships' => ['label' => '関係性', 'items' => $relationships],
                    'tags' => ['label' => 'タグ', 'items' => $tags],
                ];
            @endphp

            @foreach ($groups as $type => $group)
                <section class="mb-8">
                    <h2 class="mb-3 text-xl font-bold">
                        {{ $group['label'] }}
                    </h2>

                    @if ($group['items']->count())
                        <div class="oshi-table-wrap">
                            <table class="oshi-table">
                                <thead>
                                    <tr>
                                        <th>名称</th>
                                        <th>補足</th>
                                        <th>申請日</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($group['items'] as $item)
                                        <tr>
                                            <td>
                                                @if ($type === 'works')
                                                    {{ $item->title }}
                                                @elseif ($type === 'characters')
                                                    {{ $item->name }}
                                                @elseif ($type === 'relationships')
                                                    {{ $item->fromCharacter?->name }} → {{ $item->toCharacter?->name }}
                                                @else
                                                    {{ $item->name }}
                                                @endif
                                            </td>

                                            <td>
                                                @if ($type === 'characters')
                                                    {{ $item->work?->title }}
                                                @elseif ($type === 'relationships')
                                                    {{ $item->work?->title }} / {{ $item->relationship ?: '未設定' }}
                                                @elseif ($type === 'tags')
                                                    {{ $item->type }}
                                                @else
                                                    {{ $item->genre ?: '未設定' }}
                                                @endif
                                            </td>

                                            <td>
                                                {{ $item->updated_at?->format('Y/m/d H:i') }}
                                            </td>

                                            <td>
                                                <div class="flex flex-wrap gap-2">
                                                    <form method="POST" action="{{ route('admin.review-requests.approve') }}">
                                                        @csrf
                                                        <input type="hidden" name="type" value="{{ $type }}">
                                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                                        <button type="submit" class="oshi-btn">
                                                            公開承認
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('admin.review-requests.reject') }}">
                                                        @csrf
                                                        <input type="hidden" name="type" value="{{ $type }}">
                                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                                        <button type="submit" class="oshi-btn oshi-btn-sub">
                                                            非公開差し戻し
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="oshi-empty">
                            承認待ちの{{ $group['label'] }}はありません。
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</x-app-layout>
