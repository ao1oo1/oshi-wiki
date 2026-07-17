<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            @include('admin.partials.flash')

            <div class="oshi-story-section-page">
                <header class="oshi-story-section-header">
                    <div class="oshi-story-section-header-copy">
                        <p class="oshi-story-section-eyebrow">
                            {{ $work->title }}
                        </p>

                        <h1 class="oshi-story-section-heading">
                            {{ $section->title }}を編集
                        </h1>

                        <p class="oshi-story-section-description">
                            基本情報、物語詳細、登場キャラクターの
                            時点情報を編集します。
                        </p>
                    </div>

                    <a
                        class="oshi-btn oshi-btn-sub"
                        href="{{ route(
                            'admin.works.story-sections.show',
                            [$work, $section]
                        ) }}"
                    >
                        章の詳細へ戻る
                    </a>
                </header>

                <form
                    method="POST"
                    action="{{ route(
                        'admin.works.story-sections.update',
                        [$work, $section]
                    ) }}"
                    class="oshi-story-section-form"
                >
                    @csrf
                    @method('PUT')

                    @include(
                        'admin.work_story_sections._form'
                    )

                    <div class="oshi-story-section-actions">
                        <a
                            class="oshi-btn oshi-btn-sub"
                            href="{{ route(
                                'admin.works.story-sections.show',
                                [$work, $section]
                            ) }}"
                        >
                            キャンセル
                        </a>

                        <button
                            class="oshi-btn"
                            type="submit"
                        >
                            更新する
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</x-app-layout>
