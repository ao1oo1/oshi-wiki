@php
    $contactType = request('type');
    $contactTypeLabel = match ($contactType) {
        'data_request' => 'データ登録リクエスト',
        'contributor' => 'コントリビュータ応募',
        default => null,
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            お問い合わせ受信箱
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">
                    お問い合わせ受信箱
                </h1>
                <p class="oshi-muted">
                    最高管理者のみ閲覧できます。
                </p>
            </div>

            <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
                <div>
                    <label for="category" class="mb-1 block font-medium">種別</label>
                    <select id="category" name="category">
                        <option value="">全種別</option>
                        <option value="correction" @selected($category === 'correction')>間違いの指摘</option>
                        <option value="copyright" @selected($category === 'copyright')>著作者による削除希望</option>
                        <option value="contributor" @selected($category === 'contributor')>コントリビューター希望</option>
                        <option value="discord" @selected($category === 'discord')>開発者コミュニティ参加希望</option>
                        <option value="other" @selected($category === 'other')>その他</option>
                    </select>
                </div>

                <div>
                    <label for="read_status" class="mb-1 block font-medium">既読状態</label>
                    <select id="read_status" name="read_status">
                        <option value="">すべて</option>
                        <option value="unread" @selected($readStatus === 'unread')>未読</option>
                        <option value="read" @selected($readStatus === 'read')>既読</option>
                    </select>
                </div>

                <button type="submit" class="oshi-btn">絞り込み</button>

                <a href="{{ route('admin.contact-messages.index') }}" class="oshi-btn oshi-btn-sub">
                    解除
                </a>
            </form>

            <div class="oshi-table-wrap">
                <table class="oshi-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>種別</th>
                            <th>件名</th>
                            <th>送信者</th>
                            <th>送信日</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $message)
                            <tr>
                                <td>
                                    @if ($message->is_read)
                                        <span class="oshi-badge">既読</span>
                                    @else
                                        <span class="oshi-chip">未読</span>
                                    @endif
                                </td>
                                <td>{{ $message->categoryLabel() }}</td>
                                <td>
                                    <strong>{{ $message->subject }}</strong>
                                </td>
                                <td>
                                    {{ $message->name ?: '未入力' }}
                                    @if ($message->email)
                                        <br>
                                        <span class="oshi-muted">{{ $message->email }}</span>
                                    @endif
                                </td>
                                <td>{{ $message->created_at?->format('Y/m/d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="oshi-btn oshi-btn-sub">
                                        詳細
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="oshi-empty">
                                        お問い合わせはまだありません。
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
