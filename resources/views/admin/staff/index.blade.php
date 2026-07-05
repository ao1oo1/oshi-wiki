<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            スタッフ管理
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">スタッフ管理</h1>
                <p class="oshi-muted">
                    最高管理者のみ表示されます。スタッフの状態、一時停止、削除フラグ、備考を管理できます。
                </p>
            </div>

            <form method="POST" action="{{ route('admin.staff-management.bulk') }}">
                @csrf

                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <select name="action" required>
                        <option value="">一括操作を選択</option>
                        <option value="activate">登用中にする</option>
                        <option value="pause">一時停止にする</option>
                        <option value="delete">削除フラグを付ける</option>
                    </select>

                    <button type="submit" class="oshi-btn" onclick="return confirm('選択したスタッフに一括操作を実行します。よろしいですか？');">
                        一括実行
                    </button>
                </div>

                <div class="oshi-table-wrap">
                    <table class="oshi-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" onclick="document.querySelectorAll('.staff-check').forEach(cb => cb.checked = this.checked)">
                                </th>
                                <th>状態</th>
                                <th>ユーザーネーム</th>
                                <th>メールアドレス</th>
                                <th>Discord ID</th>
                                <th>申請日</th>
                                <th>登用開始日</th>
                                <th>登録済みの情報</th>
                                <th>登録作品件数</th>
                                <th>登録キャラクター件数</th>
                                <th>管理者備考</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($staff as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="staff-check">
                                    </td>

                                    <td>
                                        @if ($item->trashed())
                                            <span class="oshi-badge">削除フラグ</span>
                                        @elseif ($item->status === 'active')
                                            <span class="oshi-chip">登用中</span>
                                        @elseif ($item->status === 'paused')
                                            <span class="oshi-badge">一時停止</span>
                                        @elseif ($item->status === 'rejected')
                                            <span class="oshi-badge">見送り</span>
                                        @else
                                            <span class="oshi-badge">申請中</span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>{{ $item->username }}</strong>
                                    </td>

                                    <td>{{ $item->email }}</td>

                                    <td>{{ $item->discord_id ?: '未入力' }}</td>

                                    <td>{{ $item->applied_at?->format('Y/m/d H:i') ?: '未設定' }}</td>

                                    <td>{{ $item->started_at?->format('Y/m/d H:i') ?: '未設定' }}</td>

                                    <td>
                                        <a href="{{ route('admin.staff-management.registered', $item) }}" class="oshi-chip">
                                            一覧を見る
                                        </a>
                                    </td>

                                    <td>{{ $item->registered_works_count }}</td>

                                    <td>{{ $item->registered_characters_count }}</td>

                                    <td>
                                        <form method="POST" action="{{ route('admin.staff-management.notes', $item) }}">
                                            @csrf
                                            @method('PATCH')

                                            <textarea
                                                name="admin_notes"
                                                rows="3"
                                                style="min-width:220px;"
                                                placeholder="管理者用の備考"
                                            >{{ old('admin_notes', $item->admin_notes) }}</textarea>

                                            <div class="mt-2">
                                                <button type="submit" class="oshi-btn oshi-btn-sub">
                                                    保存
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11">
                                        <div class="oshi-empty">
                                            スタッフ情報はまだありません。
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-6">
                {{ $staff->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
