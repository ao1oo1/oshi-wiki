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
                            章・編を登録
                        </h1>

                        <p class="oshi-story-section-description">
                            章・編の基本情報、物語詳細、
                            登場キャラクターの時点情報を登録します。
                        </p>
                    </div>

                    <a
                        class="oshi-btn oshi-btn-sub"
                        href="{{ route(
                            'admin.works.story-sections.index',
                            $work
                        ) }}"
                    >
                        章・編一覧へ戻る
                    </a>
                </header>

                <form
                    method="POST"
                    action="{{ route(
                        'admin.works.story-sections.store',
                        $work
                    ) }}"
                    class="oshi-story-section-form"
                >
                    @csrf

                    @include(
                        'admin.work_story_sections._form'
                    )

                    <div class="oshi-story-section-actions">
                        <a
                            class="oshi-btn oshi-btn-sub"
                            href="{{ route(
                                'admin.works.story-sections.index',
                                $work
                            ) }}"
                        >
                            キャンセル
                        </a>

                        <button
                            class="oshi-btn"
                            type="submit"
                        >
                            登録する
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</x-app-layout>
