<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">提携プログラム編集</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-[#2D3748]">
                        {{ $program->name }}を編集
                    </h1>
                    <p class="mt-2 text-sm text-[#718096]">
                        URLテンプレート変更後は、既存の商品コードから生成されるURLも変わります。
                    </p>
                </div>
                <a
                    href="{{ route('admin.monetization.programs.index') }}"
                    class="oshi-btn oshi-btn-sub"
                >
                    一覧へ戻る
                </a>
            </div>

            <form
                method="POST"
                action="{{ route('admin.monetization.programs.update', $program) }}"
            >
                @method('PUT')
                @include('admin.monetization.programs._form')

                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="oshi-btn">更新する</button>
                    <a
                        href="{{ route('admin.monetization.programs.index') }}"
                        class="oshi-btn oshi-btn-sub"
                    >
                        キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
