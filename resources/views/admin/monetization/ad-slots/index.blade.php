<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">収益管理：広告スロット</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="mb-5 flex flex-wrap gap-3">
            <a
                href="{{ route('admin.monetization.services.index') }}"
                class="oshi-btn oshi-btn-sub"
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
                href="{{ route('admin.monetization.ad-slots.index') }}"
                class="oshi-btn"
            >
                広告スロット
            </a>
            <a
                href="{{ route('admin.monetization.analytics.index') }}"
                class="oshi-btn oshi-btn-sub"
            >
                クリック集計
            </a>
        </div>

        <div class="oshi-card">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-[#2D3748]">
                    インプレッション広告スロット管理
                </h1>
                <p class="mt-2 text-sm text-[#718096]">
                    登録済みの忍者AdMaxなどを、公開ページの表示位置へ割り当てます。
                </p>
            </div>

            @if ($services->isEmpty())
                <div class="mb-6 rounded-2xl bg-yellow-50 p-4 text-yellow-800">
                    先に「配信・販売サービス」で、有効な
                    インプレッション課金型サービスを登録してください。
                </div>
            @else
                <form
                    method="POST"
                    action="{{ route('admin.monetization.ad-slots.store') }}"
                    class="mb-8 rounded-3xl bg-[#FFF5F7] p-5"
                >
                    @include('admin.monetization.ad-slots._form')
                    <div class="mt-5">
                        <button type="submit" class="oshi-btn">
                            広告スロットを登録する
                        </button>
                    </div>
                </form>
            @endif

            <div class="overflow-x-auto rounded-3xl border border-[#E2E8F0]">
                <table class="w-full min-w-[1050px] text-left text-sm">
                    <thead class="bg-[#FFF5F7]">
                        <tr>
                            <th class="px-4 py-3">スロット名</th>
                            <th class="px-4 py-3">サービス</th>
                            <th class="px-4 py-3">対象ページ</th>
                            <th class="px-4 py-3">位置</th>
                            <th class="px-4 py-3">端末</th>
                            <th class="px-4 py-3 text-center">順番</th>
                            <th class="px-4 py-3 text-center">状態</th>
                            <th class="px-4 py-3 text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($slots as $slot)
                            <tr class="border-t border-[#E2E8F0]">
                                <td class="px-4 py-4 font-bold">
                                    {{ $slot->name }}
                                </td>
                                <td class="px-4 py-4">
                                    {{ $slot->service?->name ?? '削除済み' }}
                                </td>
                                <td class="px-4 py-4">
                                    {{ $pageScopes[$slot->page_scope]
                                        ?? $slot->page_scope }}
                                </td>
                                <td class="px-4 py-4">
                                    {{ $positions[$slot->position]
                                        ?? $slot->position }}
                                </td>
                                <td class="px-4 py-4">
                                    {{ $deviceTypes[$slot->device_type]
                                        ?? $slot->device_type }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    {{ $slot->priority }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    {{ $slot->is_active ? '有効' : '無効' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-center gap-2">
                                        <a
                                            href="{{ route(
                                                'admin.monetization.ad-slots.edit',
                                                $slot
                                            ) }}"
                                            class="oshi-btn oshi-btn-sub"
                                        >
                                            編集
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.monetization.ad-slots.destroy',
                                                $slot
                                            ) }}"
                                            onsubmit="return confirm(
                                                'この広告スロットを削除しますか？'
                                            );"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="oshi-btn oshi-btn-danger"
                                            >
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    class="p-8 text-center text-[#718096]"
                                >
                                    広告スロットは登録されていません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $slots->links() }}</div>
        </div>
    </div>
</x-app-layout>
