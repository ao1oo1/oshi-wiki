<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            <div class="mb-6">
                <p class="oshi-muted">{{ $work->title }}</p>
                <h1 class="oshi-admin-title">
                    章・編をテキストから登録
                </h1>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route(
                    'admin.works.story-sections.text-import.store',
                    $work
                ) }}"
                class="space-y-5"
            >
                @csrf

                <div class="oshi-card">
                    <label for="raw_text">
                        登録する章・編情報
                    </label>
                    <textarea
                        id="raw_text"
                        name="raw_text"
                        class="min-h-[600px]"
                        required
                    >{{ old('raw_text', $sampleText) }}</textarea>
                </div>

                <div class="oshi-card">
                    <label for="status">登録時の状態</label>
                    <select id="status" name="status">
                        <option value="draft">下書き</option>
                        <option value="published">公開</option>
                        <option value="private">非公開</option>
                    </select>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button class="oshi-btn" type="submit">
                        テキストから登録
                    </button>
                    <a
                        class="oshi-btn oshi-btn-sub"
                        href="{{ route(
                            'admin.works.story-sections.index',
                            $work
                        ) }}"
                    >
                        戻る
                    </a>
                </div>
            </form>
        </main>
    </div>
</x-app-layout>
