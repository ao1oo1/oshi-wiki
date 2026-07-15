<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main work-editor-page">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="oshi-admin-title">作品登録</h1>
                    <p class="oshi-muted">基本情報と作品世界の詳細を登録します。</p>
                </div>

                <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">
                    一覧へ戻る
                </a>
            </div>

            <form method="POST" action="{{ route('admin.works.store') }}">
                @csrf
                @include('admin.works._form')
            </form>
        </main>
    </div>
</x-app-layout>
