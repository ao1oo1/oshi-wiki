<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            登録済み情報
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="oshi-card">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">
                    {{ $staff->username }} さんの登録済み情報
                </h1>

                <p class="oshi-muted">
                    登録された作品・キャラクターの一覧です。
                </p>

                <div class="mt-4">
                    <a href="{{ route('admin.staff-management.index') }}" class="oshi-btn oshi-btn-sub">
                        スタッフ管理へ戻る
                    </a>
                </div>
            </div>

            <section class="mb-8">
                <h2 class="mb-3 text-xl font-bold">作品一覧</h2>

                @if ($works->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr>
                                    <th>作品名</th>
                                    <th>状態</th>
                                    <th>更新日</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($works as $work)
                                    <tr>
                                        <td>{{ $work->title }}</td>
                                        <td>{{ $work->status }}</td>
                                        <td>{{ $work->updated_at?->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.works.edit', $work) }}" class="oshi-chip">
                                                編集
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="oshi-empty">登録作品はまだありません。</div>
                @endif
            </section>

            <section>
                <h2 class="mb-3 text-xl font-bold">キャラクター一覧</h2>

                @if ($characters->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr>
                                    <th>キャラクター名</th>
                                    <th>作品</th>
                                    <th>状態</th>
                                    <th>更新日</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($characters as $character)
                                    <tr>
                                        <td>{{ $character->name }}</td>
                                        <td>{{ $character->work?->title }}</td>
                                        <td>{{ $character->status }}</td>
                                        <td>{{ $character->updated_at?->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.characters.edit', $character) }}" class="oshi-chip">
                                                編集
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="oshi-empty">登録キャラクターはまだありません。</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
