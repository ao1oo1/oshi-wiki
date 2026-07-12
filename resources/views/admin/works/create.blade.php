<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 class="oshi-admin-title">作品登録</h1>

                <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">
                    一覧へ戻る
                </a>
            </div>

            <div class="oshi-card">
                <form method="POST" action="{{ route('admin.works.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="title" class="oshi-label">タイトル</label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            class="oshi-input"
                            required
                        >
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="genre" class="oshi-label">ジャンル</label>
                        <input
                            id="genre"
                            type="text"
                            name="genre"
                            value="{{ old('genre') }}"
                            class="oshi-input"
                        >
                        @error('genre')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="original_media" class="oshi-label">原作媒体</label>
                        <input
                            id="original_media"
                            type="text"
                            name="original_media"
                            value="{{ old('original_media') }}"
                            class="oshi-input"
                            placeholder="例：漫画、アニメ、ゲーム、小説"
                        >
                        @error('original_media')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="oshi-label">状態</label>
                        <select id="status" name="status" class="oshi-input">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>下書き</option>
                            <option value="published" @selected(old('status') === 'published')>公開</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="oshi-label">説明</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="7"
                            class="oshi-input"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <p class="oshi-label">作品タグ</p>

                        <div class="oshi-work-create-tags">
                            @forelse ($tags ?? [] as $tag)
                                <label class="oshi-work-create-tag-option">
                                    <input
                                        type="checkbox"
                                        name="tag_ids[]"
                                        value="{{ $tag->id }}"
                                        @checked(in_array($tag->id, old('tag_ids', [])))
                                    >
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">登録済みのタグがありません。</p>
                            @endforelse
                        </div>

                        @error('tag_ids')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="oshi-btn oshi-btn-main">
                            登録
                        </button>

                        <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">
                            一覧へ戻る
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</x-app-layout>
