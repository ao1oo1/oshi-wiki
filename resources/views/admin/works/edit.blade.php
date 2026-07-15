<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="oshi-admin-title">作品編集</h1>
                    <p class="oshi-muted">{{ $work->title }}の作品情報を編集します。</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.works.show', $work) }}" class="oshi-btn oshi-btn-sub">作品詳細へ</a>
                    <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">作品一覧へ</a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.works.update', $work) }}">
                @csrf
                @method('PUT')
                @include('admin.works._form', ['work' => $work])
            </form>
        </main>
    </div>
</x-app-layout>
