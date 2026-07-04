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
        </div>
    </div>
</x-app-layout>
