@include('writer.original_characters._layout_start', ['title' => 'プロンプト管理'])

@php
    $filters = $filters ?? [];
@endphp

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">プロンプト管理</h2>
</div>

<div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <div>
        <p class="text-lg font-bold text-[#2D3748]">作成済みプロンプト</p>
        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
            登録数：{{ $count }} / {{ $limit === null ? '制限なし' : $limit }}
        </p>
    </div>

    @if ($limit === null || $count < $limit)
        <a href="{{ route('writer.prompts.create') }}"
           class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 text-base font-bold text-[#2D3748] shadow-sm hover:opacity-90">
            新規作成
        </a>
    @else
        <span class="inline-flex items-center justify-center rounded-2xl bg-gray-100 px-6 py-3 text-base font-bold text-gray-500">
            上限に達しています
        </span>
    @endif
</div>

<section class="mb-6 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
    <form method="GET" action="{{ route('writer.prompts.index') }}" class="space-y-5">
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">キーワード</label>
                <input type="text"
                       name="keyword"
                       value="{{ $filters['keyword'] ?? '' }}"
                       class="w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]"
                       placeholder="タイトル、用途、あらすじなど">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">作品種別</label>
                <select name="work_source"
                        class="w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    <option value="">すべて</option>
                    <option value="original" @selected(($filters['work_source'] ?? '') === 'original')>オリジナル</option>
                    <option value="v1_work" @selected(($filters['work_source'] ?? '') === 'v1_work')>登録済み作品</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">作品名</label>
                <select name="work_id"
                        class="w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    <option value="">すべて</option>
                    @foreach ($works as $work)
                        <option value="{{ $work->id }}" @selected((string) ($filters['work_id'] ?? '') === (string) $work->id)>
                            {{ $work->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">作風</label>
                <select name="writing_style"
                        class="w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    <option value="">すべて</option>
                    @foreach ($writingStyleLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['writing_style'] ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">ジャンル</label>
                <select name="genre"
                        class="w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    <option value="">すべて</option>
                    @foreach ($genreLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['genre'] ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">状態</label>
                <select name="status"
                        class="w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    <option value="">すべて</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>有効</option>
                    <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>下書き</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-[#2D3748]">並び替え</label>
                <select name="sort"
                        class="w-full rounded-2xl border-[#E2E8F0] bg-white px-4 py-3 text-[#2D3748] shadow-sm focus:border-[#FED7E2] focus:ring-[#FED7E2]">
                    @php($sort = $filters['sort'] ?? 'latest')
                    <option value="latest" @selected($sort === 'latest')>新しい順</option>
                    <option value="oldest" @selected($sort === 'oldest')>古い順</option>
                    <option value="updated" @selected($sort === 'updated')>更新が新しい順</option>
                    <option value="most_used" @selected($sort === 'most_used')>よく使う順</option>
                    <option value="recently_used" @selected($sort === 'recently_used')>最近使った順</option>
                    <option value="title_asc" @selected($sort === 'title_asc')>タイトル昇順</option>
                    <option value="title_desc" @selected($sort === 'title_desc')>タイトル降順</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-2xl bg-[#FED7E2] px-6 py-3 text-base font-bold text-[#2D3748] shadow-sm hover:opacity-90">
                検索する
            </button>

            <a href="{{ route('writer.prompts.index') }}"
               class="rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 text-base font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                リセット
            </a>
        </div>
    </form>
</section>

<div class="mb-4 flex flex-col justify-between gap-2 text-sm font-bold text-[#A0AEC0] md:flex-row md:items-center">
    <div>表示件数：{{ $savedPrompts->total() }}件</div>
    <div>
        並び順：
        @php($sortLabel = [
            'latest' => '新しい順',
            'oldest' => '古い順',
            'updated' => '更新が新しい順',
            'most_used' => 'よく使う順',
            'recently_used' => '最近使った順',
            'title_asc' => 'タイトル昇順',
            'title_desc' => 'タイトル降順',
        ][$filters['sort'] ?? 'latest'] ?? '新しい順')
        {{ $sortLabel }}
    </div>
</div>

<div class="overflow-hidden rounded-3xl border border-[#E2E8F0] bg-white shadow-sm">
    <table class="w-full table-auto text-left text-sm">
        <thead class="bg-[#F7FAFC] text-[#A0AEC0]">
            <tr>
                <th class="px-6 py-4">タイトル</th>
                <th class="px-6 py-4">作品</th>
                <th class="px-6 py-4">作風</th>
                <th class="px-6 py-4">ジャンル</th>
                <th class="px-6 py-4">状態</th>
                <th class="px-6 py-4">利用</th>
                <th class="px-6 py-4">操作</th>
            </tr>
        </thead>
        <tbody class="text-[#2D3748]">
            @forelse ($savedPrompts as $prompt)
                <tr class="border-t border-[#E2E8F0]">
                    <td class="px-6 py-5 text-lg font-bold">{{ $prompt->title }}</td>
                    <td class="px-6 py-5">{{ $prompt->workLabel() }}</td>
                    <td class="px-6 py-5">{{ $prompt->writingStyleLabel() }}</td>
                    <td class="px-6 py-5">{{ $prompt->genreLabel() }}</td>
                    <td class="px-6 py-5">
                        <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                            {{ $prompt->status === 'active' ? '有効' : '下書き' }}
                        </span>
                    </td>
                    <td class="px-6 py-5">
                        <div class="font-bold text-[#2D3748]">{{ number_format($prompt->used_count ?? 0) }}回</div>
                        <div class="mt-1 text-xs font-bold text-[#A0AEC0]">{{ $prompt->lastUsedLabel() }}</div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('writer.prompts.show', $prompt) }}"
                               class="rounded-xl border border-[#CBD5E0] px-4 py-2 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                                詳細
                            </a>
                            <a href="{{ route('writer.prompts.edit', $prompt) }}"
                               class="rounded-xl bg-[#FED7E2] px-4 py-2 font-bold text-[#2D3748] hover:opacity-90">
                                編集
                            </a>

                            <form method="POST" action="{{ route('writer.prompts.duplicate', $prompt) }}">
                                @csrf
                                <button type="submit"
                                        class="rounded-xl border border-[#CBD5E0] bg-white px-4 py-2 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                                    複製
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <p class="text-lg font-bold text-[#2D3748]">条件に一致するプロンプトがありません。</p>
                        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">検索条件を変更するか、新規作成してください。</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $savedPrompts->links() }}
</div>

@include('writer.original_characters._layout_end')
