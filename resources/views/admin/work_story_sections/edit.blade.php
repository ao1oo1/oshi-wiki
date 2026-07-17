<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')
        <main class="oshi-admin-main">
            <h1 class="oshi-admin-title">
                {{ $work->title }}：{{ $section->title }}を編集
            </h1>

            <form method="POST" action="{{ route('admin.works.story-sections.update', [$work, $section]) }}">
                @csrf
                @method('PUT')
                @include('admin.work_story_sections._form')
                <div class="mt-6 flex gap-3">
                    <button class="oshi-btn" type="submit">更新する</button>
                    <a class="oshi-btn oshi-btn-sub" href="{{ route('admin.works.story-sections.show', [$work, $section]) }}">
                        戻る
                    </a>
                </div>
            </form>
        </main>
    </div>
</x-app-layout>
