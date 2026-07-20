<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">配信・販売サービス編集</h2></x-slot>
    <div class="p-6">
        @include('admin.partials.flash')
        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div><h1 class="text-2xl font-bold text-[#2D3748]">{{ $service->name }}を編集</h1><p class="mt-2 text-sm text-[#718096]">URLテンプレートとアフィリエイトタグは、次工程の提携プログラム管理で設定します。</p></div>
                <a href="{{ route('admin.monetization.services.index') }}" class="oshi-btn oshi-btn-sub">一覧へ戻る</a>
            </div>
            <form method="POST" action="{{ route('admin.monetization.services.update', $service) }}">
                @method('PUT')
                @include('admin.monetization.services._form')
                <div class="mt-6 flex flex-wrap gap-3"><button type="submit" class="oshi-btn">更新する</button><a href="{{ route('admin.monetization.services.index') }}" class="oshi-btn oshi-btn-sub">キャンセル</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
