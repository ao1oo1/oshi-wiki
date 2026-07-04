@if ($errors->any())
    <div class="mb-4 rounded bg-red-100 px-4 py-3 text-red-800">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-4">
    <label for="title" class="mb-1 block font-medium">作品名</label>
    <input
        id="title"
        type="text"
        name="title"
        value="{{ old('title', $work->title ?? '') }}"
        class="w-full rounded border-gray-300"
        required
    >
</div>

<div class="mb-4">
    <label for="title_kana" class="mb-1 block font-medium">読み仮名</label>
    <input
        id="title_kana"
        type="text"
        name="title_kana"
        value="{{ old('title_kana', $work->title_kana ?? '') }}"
        class="w-full rounded border-gray-300"
    >
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="mb-4">
        <label for="genre" class="mb-1 block font-medium">ジャンル</label>
        <input
            id="genre"
            type="text"
            name="genre"
            value="{{ old('genre', $work->genre ?? '') }}"
            class="w-full rounded border-gray-300"
            placeholder="例：漫画、アニメ、ゲーム、小説"
        >
    </div>

    <div class="mb-4">
        <label for="original_media" class="mb-1 block font-medium">原作媒体</label>
        <input
            id="original_media"
            type="text"
            name="original_media"
            value="{{ old('original_media', $work->original_media ?? '') }}"
            class="w-full rounded border-gray-300"
            placeholder="例：漫画、アニメ、ゲーム"
        >
    </div>
</div>

<div class="mb-4">
    <label for="official_url" class="mb-1 block font-medium">公式URL</label>
    <input
        id="official_url"
        type="url"
        name="official_url"
        value="{{ old('official_url', $work->official_url ?? '') }}"
        class="w-full rounded border-gray-300"
        placeholder="https://example.com"
    >
</div>

<div class="mb-4">
    <label for="guideline_url" class="mb-1 block font-medium">ガイドラインURL</label>
    <input
        id="guideline_url"
        type="url"
        name="guideline_url"
        value="{{ old('guideline_url', $work->guideline_url ?? '') }}"
        class="w-full rounded border-gray-300"
        placeholder="https://example.com/guideline"
    >
</div>

<div class="mb-4">
    <label for="description" class="mb-1 block font-medium">説明</label>
    <textarea
        id="description"
        name="description"
        rows="5"
        class="w-full rounded border-gray-300"
    >{{ old('description', $work->description ?? '') }}</textarea>
</div>


<div class="mb-6">
    <label class="mb-2 block font-medium">タグ</label>

    @if (($tags ?? collect())->count())
        <div class="grid grid-cols-1 gap-2 rounded border border-gray-200 p-3 md:grid-cols-3">
            @foreach ($tags as $tag)
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="tag_ids[]"
                        value="{{ $tag->id }}"
                        @checked(in_array($tag->id, old('tag_ids', isset($work) ? $work->tags->pluck('id')->toArray() : [])))
                    >
                    <span>{{ $tag->name }}</span>
                </label>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-600">
            まだタグが登録されていません。先にタグ管理から登録してください。
        </p>
    @endif
</div>

@if (auth()->user()?->isSuperAdmin())
<div class="mb-6">
    <label for="status" class="mb-1 block font-medium">状態</label>
    <p class="mb-2 text-sm text-gray-600">
        公開ページに表示したい場合は「公開」を選択してください。
    </p>
    <select
        id="status"
        name="status"
        class="w-full rounded border-gray-300"
    >
        <option value="draft" @selected(old('status', $work->status ?? 'draft') === 'draft')>下書き</option>
        <option value="published" @selected(old('status', $work->status ?? '') === 'published')>公開</option>
        <option value="private" @selected(old('status', $work->status ?? '') === 'private')>非公開</option>
    </select>
</div>
@else
    <div class="mb-6 rounded bg-pink-50 p-4 text-sm">
        情報入力スタッフによる登録・編集は、最高管理者への承認申請として保存されます。
    </div>
@endif

<button
    type="submit"
    style="display:inline-block;background:#2563eb;color:#ffffff;padding:10px 24px;border-radius:8px;font-weight:bold;border:none;cursor:pointer;"
>
    保存する
</button>
