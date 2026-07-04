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
    <label for="work_id" class="mb-1 block font-medium">作品</label>
    <select
        id="work_id"
        name="work_id"
        class="w-full rounded border-gray-300"
        required
    >
        <option value="">選択してください</option>
        @foreach ($works as $work)
            <option
                value="{{ $work->id }}"
                @selected(old('work_id', $character->work_id ?? $selectedWorkId ?? '') == $work->id)
            >
                {{ $work->title }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label for="name" class="mb-1 block font-medium">キャラクター名</label>
    <input
        id="name"
        type="text"
        name="name"
        value="{{ old('name', $character->name ?? '') }}"
        class="w-full rounded border-gray-300"
        required
    >
</div>

<div class="mb-4">
    <label for="name_kana" class="mb-1 block font-medium">読み仮名</label>
    <input
        id="name_kana"
        type="text"
        name="name_kana"
        value="{{ old('name_kana', $character->name_kana ?? '') }}"
        class="w-full rounded border-gray-300"
    >
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="mb-4">
        <label for="age" class="mb-1 block font-medium">年齢</label>
        <input
            id="age"
            type="text"
            name="age"
            value="{{ old('age', $character->age ?? '') }}"
            class="w-full rounded border-gray-300"
        >
    </div>

    <div class="mb-4">
        <label for="first_person" class="mb-1 block font-medium">一人称</label>
        <input
            id="first_person"
            type="text"
            name="first_person"
            value="{{ old('first_person', $character->first_person ?? '') }}"
            class="w-full rounded border-gray-300"
        >
    </div>
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="mb-4">
        <label for="affiliation" class="mb-1 block font-medium">所属</label>
        <input
            id="affiliation"
            type="text"
            name="affiliation"
            value="{{ old('affiliation', $character->affiliation ?? '') }}"
            class="w-full rounded border-gray-300"
        >
    </div>

    <div class="mb-4">
        <label for="grade_class" class="mb-1 block font-medium">学年クラス</label>
        <input
            id="grade_class"
            type="text"
            name="grade_class"
            value="{{ old('grade_class', $character->grade_class ?? '') }}"
            class="w-full rounded border-gray-300"
        >
    </div>
</div>

<div class="mb-4">
    <label for="tone" class="mb-1 block font-medium">口調</label>
    <textarea
        id="tone"
        name="tone"
        rows="3"
        class="w-full rounded border-gray-300"
    >{{ old('tone', $character->tone ?? '') }}</textarea>
</div>

<div class="mb-4">
    <label for="tone_examples" class="mb-1 block font-medium">口調の例</label>
    <textarea
        id="tone_examples"
        name="tone_examples"
        rows="3"
        class="w-full rounded border-gray-300"
    >{{ old('tone_examples', $character->tone_examples ?? '') }}</textarea>
</div>

<div class="mb-4">
    <label for="personality" class="mb-1 block font-medium">性格</label>
    <textarea
        id="personality"
        name="personality"
        rows="3"
        class="w-full rounded border-gray-300"
    >{{ old('personality', $character->personality ?? '') }}</textarea>
</div>

<div class="mb-4">
    <label for="appearance" class="mb-1 block font-medium">外見の特徴</label>
    <textarea
        id="appearance"
        name="appearance"
        rows="3"
        class="w-full rounded border-gray-300"
    >{{ old('appearance', $character->appearance ?? '') }}</textarea>
</div>

<div class="mb-4">
    <label for="background" class="mb-1 block font-medium">背景・経歴</label>
    <textarea
        id="background"
        name="background"
        rows="3"
        class="w-full rounded border-gray-300"
    >{{ old('background', $character->background ?? '') }}</textarea>
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
                        @checked(in_array($tag->id, old('tag_ids', isset($character) ? $character->tags->pluck('id')->toArray() : [])))
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
        <option value="draft" @selected(old('status', $character->status ?? 'draft') === 'draft')>下書き</option>
        <option value="published" @selected(old('status', $character->status ?? '') === 'published')>公開</option>
        <option value="private" @selected(old('status', $character->status ?? '') === 'private')>非公開</option>
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
