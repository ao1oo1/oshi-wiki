<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')
        <main class="oshi-admin-main">
            <h1 class="oshi-admin-title">
                {{ $work->title }}：章・編を登録
            </h1>

            <form method="POST" action="{{ route('admin.works.story-sections.store', $work) }}">
                @csrf
                @include('admin.work_story_sections._form')
                <div class="mt-6 flex gap-3">
                    <button class="oshi-btn" type="submit">登録する</button>
                    <a class="oshi-btn oshi-btn-sub" href="{{ route('admin.works.story-sections.index', $work) }}">
                        戻る
                    </a>
                </div>
            </form>
        </main>
    </div>
</x-app-layout>
