<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            お問い合わせ詳細
        </h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.contact-messages.index') }}" class="oshi-btn oshi-btn-sub">
                受信箱へ戻る
            </a>

            <form method="POST" action="{{ route('admin.contact-messages.mark-unread', $message) }}">
                @csrf
                <button type="submit" class="oshi-btn oshi-btn-sub">
                    未読に戻す
                </button>
            </form>

            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm('このお問い合わせに削除フラグを付けます。よろしいですか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="oshi-btn oshi-btn-sub">
                    削除フラグをつける
                </button>
            </form>
        </div>

        <div class="oshi-card">
            <h1 class="mb-4 text-2xl font-bold">
                {{ $message->subject }}
            </h1>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <h2 class="mb-1 font-semibold">種別</h2>
                    <p class="rounded bg-gray-50 p-3">{{ $message->categoryLabel() }}</p>
                </div>

                <div>
                    <h2 class="mb-1 font-semibold">送信日</h2>
                    <p class="rounded bg-gray-50 p-3">{{ $message->created_at?->format('Y/m/d H:i') }}</p>
                </div>

                <div>
                    <h2 class="mb-1 font-semibold">お名前</h2>
                    <p class="rounded bg-gray-50 p-3">{{ $message->name ?: '未入力' }}</p>
                </div>

                <div>
                    <h2 class="mb-1 font-semibold">メールアドレス</h2>
                    <p class="rounded bg-gray-50 p-3">{{ $message->email ?: '未入力' }}</p>
                </div>
            </div>

            @if ($message->target_url)
                <div class="mb-6">
                    <h2 class="mb-1 font-semibold">対象URL</h2>
                    <p class="rounded bg-gray-50 p-3">
                        <a href="{{ $message->target_url }}" target="_blank" rel="noopener noreferrer">
                            {{ $message->target_url }}
                        </a>
                    </p>
                </div>
            @endif

            <div>
                <h2 class="mb-1 font-semibold">内容</h2>
                <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">
                    {{ $message->body }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
