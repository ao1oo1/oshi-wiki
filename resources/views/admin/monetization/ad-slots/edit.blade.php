<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">広告スロット編集</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6 flex items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-[#2D3748]">
                    {{ $adSlot->name }}を編集
                </h1>
                <a
                    href="{{ route('admin.monetization.ad-slots.index') }}"
                    class="oshi-btn oshi-btn-sub"
                >
                    一覧へ戻る
                </a>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'admin.monetization.ad-slots.update',
                    $adSlot
                ) }}"
            >
                @method('PUT')
                @include('admin.monetization.ad-slots._form')

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="oshi-btn">更新する</button>
                    <a
                        href="{{ route(
                            'admin.monetization.ad-slots.index'
                        ) }}"
                        class="oshi-btn oshi-btn-sub"
                    >
                        キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
