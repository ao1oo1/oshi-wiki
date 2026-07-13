<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Oshi-Wiki 管理ダッシュボード
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="rounded bg-white p-6 shadow">
            <p class="mb-4">ログインしました。</p>

            <a href="{{ route('admin.works.index') }}"
               class="inline-block rounded bg-blue-600 px-4 py-2 text-white">
                作品管理へ
            </a>

            <div class="mt-4 rounded-2xl border border-[#FED7E2] bg-[#FFF5F7] p-4">
                <p class="mb-3 font-bold text-[#2D3748]">
                    管理スタッフの方へ
                </p>
                <p class="mb-4 text-sm leading-7 text-[#4A5568]">
                    情報登録時のルールや公開までの流れをまとめています。登録作業の前にご確認ください。
                </p>
                <a href="{{ route('admin.staff-guide') }}"
                   class="inline-flex rounded-full bg-[#FED7E2] px-5 py-2 font-bold text-[#2D3748]">
                    管理スタッフ向けご案内を見る
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
