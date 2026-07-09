@include('writer.original_characters._layout_start', ['title' => '保存プロンプト'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">保存プロンプト</h2>
</div>

<div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <div>
        <p class="text-lg font-bold text-[#2D3748]">保存済みプロンプト</p>
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

<div class="overflow-hidden rounded-3xl border border-[#E2E8F0] bg-white shadow-sm">
    <table class="w-full table-auto text-left text-sm">
        <thead class="bg-[#F7FAFC] text-[#A0AEC0]">
            <tr>
                <th class="px-6 py-4">タイトル</th>
                <th class="px-6 py-4">作品</th>
                <th class="px-6 py-4">作風</th>
                <th class="px-6 py-4">ジャンル</th>
                <th class="px-6 py-4">状態</th>
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
                    <td colspan="6" class="px-6 py-16 text-center">
                        <p class="text-lg font-bold text-[#2D3748]">まだプロンプトが保存されていません。</p>
                        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">条件を入力して、AIに貼り付けるプロンプトを作成できます。</p>
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
