@csrf

@php
    $inputClass = 'w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]';
    $labelClass = 'mb-2 block text-base font-bold text-[#2D3748]';
    $textareaClass = 'w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]';
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="{{ $labelClass }}">名前 <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $originalCharacter->name ?? '') }}" class="{{ $inputClass }}" required>
    </div>

    <div>
        <label class="{{ $labelClass }}">読み仮名</label>
        <input type="text" name="name_kana" value="{{ old('name_kana', $originalCharacter->name_kana ?? '') }}" class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">年齢</label>
        <input type="text" name="age" value="{{ old('age', $originalCharacter->age ?? '') }}" class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">性別</label>
        <input type="text" name="gender" value="{{ old('gender', $originalCharacter->gender ?? '') }}" class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">所属</label>
        <input type="text" name="affiliation" value="{{ old('affiliation', $originalCharacter->affiliation ?? '') }}" class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">学年・クラス</label>
        <input type="text" name="school_grade" value="{{ old('school_grade', $originalCharacter->school_grade ?? '') }}" class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">一人称</label>
        <input type="text" name="first_person" value="{{ old('first_person', $originalCharacter->first_person ?? '') }}" class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">状態</label>
        <select name="status" class="{{ $inputClass }}">
            @php($status = old('status', $originalCharacter->status ?? 'active'))
            <option value="active" @selected($status === 'active')>有効</option>
            <option value="draft" @selected($status === 'draft')>下書き</option>
        </select>
    </div>
</div>

<div class="mt-6 rounded-2xl bg-[#FFF1F5] px-5 py-4">
    <label class="inline-flex items-center gap-3">
        <input type="checkbox" name="is_main_character" value="1" @checked(old('is_main_character', $originalCharacter->is_main_character ?? false)) class="rounded border-[#CBD5E0] text-[#FED7E2] focus:ring-[#FED7E2]">
        <span class="text-base font-bold text-[#2D3748]">主人公・夢主として扱う</span>
    </label>
</div>

<div class="mt-8 grid gap-6">
    <div>
        <label class="{{ $labelClass }}">口調</label>
        <textarea name="speech_style" rows="5" class="{{ $textareaClass }}">{{ old('speech_style', $originalCharacter->speech_style ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">口調例</label>
        <textarea name="speech_examples" rows="5" class="{{ $textareaClass }}">{{ old('speech_examples', $originalCharacter->speech_examples ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">性格・特徴</label>
        <textarea name="personality" rows="6" class="{{ $textareaClass }}">{{ old('personality', $originalCharacter->personality ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">外見</label>
        <textarea name="appearance" rows="6" class="{{ $textareaClass }}">{{ old('appearance', $originalCharacter->appearance ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">背景・経歴</label>
        <textarea name="background" rows="6" class="{{ $textareaClass }}">{{ old('background', $originalCharacter->background ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">絶対に守りたい設定</label>
        <textarea name="important_points" rows="5" class="{{ $textareaClass }}">{{ old('important_points', $originalCharacter->important_points ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">NG設定・避けたい表現</label>
        <textarea name="ng_points" rows="5" class="{{ $textareaClass }}">{{ old('ng_points', $originalCharacter->ng_points ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">備考</label>
        <textarea name="notes" rows="5" class="{{ $textareaClass }}">{{ old('notes', $originalCharacter->notes ?? '') }}</textarea>
    </div>
</div>

<div class="mt-8 flex flex-wrap items-center gap-3">
    <button type="submit" class="rounded-2xl bg-[#FED7E2] px-6 py-3 text-base font-bold text-[#2D3748] shadow-sm hover:opacity-90">
        保存する
    </button>
    <a href="{{ route('writer.original-characters.index') }}" class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 text-base font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
        一覧へ戻る
    </a>
</div>
