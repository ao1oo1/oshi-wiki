<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">収益管理：Google AdSense</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="mb-5 flex flex-wrap gap-3">
            <a href="{{ route('admin.monetization.services.index') }}" class="oshi-btn oshi-btn-sub">配信・販売サービス</a>
            <a href="{{ route('admin.monetization.programs.index') }}" class="oshi-btn oshi-btn-sub">提携プログラム</a>
            <a href="{{ route('admin.monetization.ad-slots.index') }}" class="oshi-btn oshi-btn-sub">広告スロット</a>
            <a href="{{ route('admin.monetization.adsense.edit') }}" class="oshi-btn">Google AdSense</a>
            <a href="{{ route('admin.monetization.analytics.index') }}" class="oshi-btn oshi-btn-sub">クリック集計</a>
        </div>

        <div class="oshi-card">
            <h1 class="text-2xl font-bold text-[#2D3748]">Google AdSense設定</h1>
            <p class="mt-2 text-sm text-[#718096]">
                公開ページのheadに読み込むGoogle AdSenseコードを一元管理します。
                スクリプト全文ではなく、パブリッシャーIDだけを保存します。
            </p>

            @if ($errors->any())
                <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.monetization.adsense.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="publisher_id" class="mb-2 block text-sm font-bold text-[#4A5568]">パブリッシャーID</label>
                    <input
                        id="publisher_id"
                        name="publisher_id"
                        type="text"
                        value="{{ old('publisher_id', $setting->publisher_id) }}"
                        class="w-full max-w-xl rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3"
                        placeholder="ca-pub-3916030283806562"
                        required
                    >
                </div>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="is_enabled" value="1" class="mt-1 rounded border-[#CBD5E0]" @checked(old('is_enabled', $setting->is_enabled))>
                    <span>
                        <span class="block font-bold text-[#2D3748]">AdSenseを有効にする</span>
                        <span class="block text-sm text-[#718096]">OFFにすると公開ページからAdSense本体スクリプトを出力しません。</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="auto_ads_enabled" value="1" class="mt-1 rounded border-[#CBD5E0]" @checked(old('auto_ads_enabled', $setting->auto_ads_enabled))>
                    <span>
                        <span class="block font-bold text-[#2D3748]">自動広告を利用する</span>
                        <span class="block text-sm text-[#718096]">AdSense側の自動広告設定と組み合わせて利用します。</span>
                    </span>
                </label>

                <button type="submit" class="oshi-btn">AdSense設定を保存</button>
            </form>
        </div>
    </div>
</x-app-layout>
