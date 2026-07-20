<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">収益管理：配信・販売サービス</h2></x-slot>
    <div class="p-6">
        @include('admin.partials.flash')

        <div class="mb-5 flex flex-wrap gap-3">
            <a
                href="{{ route('admin.monetization.services.index') }}"
                class="oshi-btn"
            >
                配信・販売サービス
            </a>
            <a
                href="{{ route('admin.monetization.programs.index') }}"
                class="oshi-btn oshi-btn-sub"
            >
                提携プログラム
            </a>
            <a
                href="{{ route('admin.monetization.analytics.index') }}"
                class="oshi-btn oshi-btn-sub"
            >
                クリック集計
            </a>
        </div>
        <div class="oshi-card admin-index-shell">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-[#2D3748]">配信・販売サービス管理</h1>
                <p class="mt-2 text-sm text-[#718096]">DMM TV、電子書籍ストア、商品販売サイトなどを登録します。アフィリエイトURLやタグは、この画面では登録しません。</p>
            </div>
            <form method="POST" action="{{ route('admin.monetization.services.store') }}" class="mb-8 rounded-3xl bg-[#FFF5F7] p-5">
                @include('admin.monetization.services._form')
                <div class="mt-5"><button type="submit" class="oshi-btn">サービスを登録する</button></div>
            </form>
            <form method="GET" action="{{ route('admin.monetization.services.index') }}" class="mb-6 rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5 admin-index-filter-form">
                <div class="admin-index-filter-grid">
                    <div><label for="keyword" class="mb-1 block text-sm font-bold text-[#4A5568]">キーワード</label><input id="keyword" type="text" name="keyword" value="{{ $keyword }}" placeholder="サービス名・識別子・説明" class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"></div>
                    <div><label for="category" class="mb-1 block text-sm font-bold text-[#4A5568]">カテゴリ</label><select id="category" name="category" class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"><option value="">すべて</option>@foreach ($categories as $value => $label)<option value="{{ $value }}" @selected($selectedCategory === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div><label for="active_status" class="mb-1 block text-sm font-bold text-[#4A5568]">利用状態</label><select id="active_status" name="active_status" class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"><option value="">すべて</option><option value="active" @selected($selectedActiveStatus === 'active')>有効</option><option value="inactive" @selected($selectedActiveStatus === 'inactive')>無効</option></select></div>
                    <button type="submit" class="oshi-btn">検索</button>
                    <a href="{{ route('admin.monetization.services.index') }}" class="oshi-btn oshi-btn-sub text-center">クリア</a>
                </div>
            </form>
            @include('admin.partials.list-result-count', ['items' => $services, 'totalCount' => $totalCount])
            <div class="overflow-x-auto rounded-3xl border border-[#E2E8F0] bg-white">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-[#FFF5F7] text-[#2D3748]"><tr><th class="px-4 py-3">ID</th><th class="px-4 py-3">サービス名</th><th class="px-4 py-3">カテゴリ</th><th class="px-4 py-3">識別子</th><th class="px-4 py-3 text-center">優先順位</th><th class="px-4 py-3 text-center">状態</th><th class="px-4 py-3 text-center">操作</th></tr></thead>
                    <tbody>
                    @forelse ($services as $service)
                        <tr class="border-t border-[#E2E8F0]">
                            <td class="px-4 py-4 font-semibold text-[#4A5568]">{{ $service->id }}</td>
                            <td class="px-4 py-4"><p class="font-bold text-[#2D3748]">{{ $service->name }}</p>@if ($service->default_button_label)<p class="mt-1 text-xs text-[#718096]">{{ $service->default_button_label }}</p>@endif</td>
                            <td class="px-4 py-4">{{ $categories[$service->category] ?? $service->category }}</td>
                            <td class="px-4 py-4 font-mono text-xs">{{ $service->slug }}</td>
                            <td class="px-4 py-4 text-center">{{ $service->priority }}</td>
                            <td class="px-4 py-4 text-center"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $service->is_active ? '有効' : '無効' }}</span></td>
                            <td class="px-4 py-4"><div class="flex justify-center gap-2"><a href="{{ route('admin.monetization.services.edit', $service) }}" class="oshi-btn oshi-btn-sub">編集</a><form method="POST" action="{{ route('admin.monetization.services.destroy', $service) }}" onsubmit="return confirm('このサービスを削除しますか？');">@csrf @method('DELETE')<button type="submit" class="oshi-btn oshi-btn-danger">削除</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-[#718096]">配信・販売サービスは登録されていません。</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $services->links() }}</div>
        </div>
    </div>
</x-app-layout>
