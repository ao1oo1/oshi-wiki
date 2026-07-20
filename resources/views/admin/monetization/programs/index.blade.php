<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">収益管理：提携プログラム</h2>
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
                class="oshi-btn"
            >
                提携プログラム
            </a>
        </div>

        <div class="oshi-card admin-index-shell">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-[#2D3748]">
                    提携プログラム管理
                </h1>
                <p class="mt-2 text-sm leading-7 text-[#718096]">
                    ASP、アフィリエイト識別子、URLテンプレート、許可ホストを管理します。
                    完成URLは保存せず、商品コードから表示時に生成します。
                </p>
            </div>

            @if ($services->isEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-800">
                    先に配信・販売サービスを登録してください。
                </div>
            @else
                <form
                    method="POST"
                    action="{{ route('admin.monetization.programs.store') }}"
                    class="mb-8 rounded-3xl bg-[#FFF5F7] p-5"
                >
                    @include('admin.monetization.programs._form')
                    <div class="mt-6">
                        <button type="submit" class="oshi-btn">
                            提携プログラムを登録する
                        </button>
                    </div>
                </form>
            @endif

            <form
                method="GET"
                action="{{ route('admin.monetization.programs.index') }}"
                class="mb-6 rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5 admin-index-filter-form"
            >
                <div class="admin-index-filter-grid">
                    <div>
                        <label for="keyword" class="oshi-label">キーワード</label>
                        <input
                            id="keyword"
                            type="text"
                            name="keyword"
                            value="{{ $keyword }}"
                            class="oshi-input"
                            placeholder="プログラム名・ASP・識別子"
                        >
                    </div>

                    <div>
                        <label for="filter_service_id" class="oshi-label">
                            サービス
                        </label>
                        <select
                            id="filter_service_id"
                            name="service_id"
                            class="oshi-input"
                        >
                            <option value="">すべて</option>
                            @foreach ($services as $service)
                                <option
                                    value="{{ $service->id }}"
                                    @selected(
                                        (int) $selectedServiceId
                                        === (int) $service->id
                                    )
                                >
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="active_status" class="oshi-label">
                            利用状態
                        </label>
                        <select
                            id="active_status"
                            name="active_status"
                            class="oshi-input"
                        >
                            <option value="">すべて</option>
                            <option value="active" @selected($selectedActiveStatus === 'active')>有効</option>
                            <option value="inactive" @selected($selectedActiveStatus === 'inactive')>無効</option>
                        </select>
                    </div>

                    <button type="submit" class="oshi-btn">検索</button>
                    <a
                        href="{{ route('admin.monetization.programs.index') }}"
                        class="oshi-btn oshi-btn-sub text-center"
                    >
                        クリア
                    </a>
                </div>
            </form>

            @include(
                'admin.partials.list-result-count',
                ['items' => $programs, 'totalCount' => $totalCount]
            )

            <div class="overflow-x-auto rounded-3xl border border-[#E2E8F0] bg-white">
                <table class="w-full min-w-[1100px] text-left text-sm">
                    <thead class="bg-[#FFF5F7] text-[#2D3748]">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">サービス</th>
                            <th class="px-4 py-3">プログラム</th>
                            <th class="px-4 py-3">提供元</th>
                            <th class="px-4 py-3 text-center">区分</th>
                            <th class="px-4 py-3 text-center">既定</th>
                            <th class="px-4 py-3 text-center">状態</th>
                            <th class="px-4 py-3 text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programs as $program)
                            <tr class="border-t border-[#E2E8F0]">
                                <td class="px-4 py-4">{{ $program->id }}</td>
                                <td class="px-4 py-4 font-bold">
                                    {{ $program->service?->name }}
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-[#2D3748]">
                                        {{ $program->name }}
                                    </p>
                                    <p class="mt-1 max-w-md truncate font-mono text-xs text-[#718096]">
                                        {{ $program->url_template }}
                                    </p>
                                </td>
                                <td class="px-4 py-4">
                                    {{ $program->provider_name ?: '—' }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    {{ $program->is_affiliate ? '広告' : '公式' }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    {{ $program->is_default ? '既定' : '—' }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    {{ $program->is_active ? '有効' : '無効' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-center gap-2">
                                        <a
                                            href="{{ route('admin.monetization.programs.edit', $program) }}"
                                            class="oshi-btn oshi-btn-sub"
                                        >
                                            編集
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.monetization.programs.destroy', $program) }}"
                                            onsubmit="return confirm('この提携プログラムを削除しますか？');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="oshi-btn oshi-btn-danger">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-[#718096]">
                                    提携プログラムは登録されていません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $programs->links() }}</div>
        </div>
    </div>
</x-app-layout>
