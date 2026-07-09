@include('writer.original_characters._layout_start', ['title' => 'オリジナルキャラクター'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">オリジナルキャラクター</h2>
</div>

<div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <div>
        <p class="text-lg font-bold text-[#2D3748]">登録キャラクター一覧</p>
        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
            登録数：{{ $count }} / {{ $limit === null ? '制限なし' : $limit }}
        </p>
    </div>

    @if ($limit === null || $count < $limit)
        <a href="{{ route('writer.original-characters.create') }}"
           class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 text-base font-bold text-[#2D3748] shadow-sm hover:opacity-90">
            新規登録
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
                <th class="px-6 py-4">名前</th>
                <th class="px-6 py-4">読み仮名</th>
                <th class="px-6 py-4">年齢</th>
                <th class="px-6 py-4">一人称</th>
                <th class="px-6 py-4">状態</th>
                <th class="px-6 py-4">操作</th>
            </tr>
        </thead>
        <tbody class="text-[#2D3748]">
            @forelse ($originalCharacters as $character)
                <tr class="border-t border-[#E2E8F0]">
                    <td class="px-6 py-5 text-lg font-bold">{{ $character->name }}</td>
                    <td class="px-6 py-5">{{ $character->name_kana ?: '-' }}</td>
                    <td class="px-6 py-5">{{ $character->age ?: '-' }}</td>
                    <td class="px-6 py-5">{{ $character->first_person ?: '-' }}</td>
                    <td class="px-6 py-5">
                        <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                            {{ $character->status === 'active' ? '有効' : '下書き' }}
                        </span>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('writer.original-characters.show', $character) }}"
                               class="rounded-xl border border-[#CBD5E0] px-4 py-2 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                                詳細
                            </a>
                            <a href="{{ route('writer.original-characters.edit', $character) }}"
                               class="rounded-xl bg-[#FED7E2] px-4 py-2 font-bold text-[#2D3748] hover:opacity-90">
                                編集
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <p class="text-lg font-bold text-[#2D3748]">まだオリジナルキャラクターが登録されていません。</p>
                        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">まずは新規登録からキャラクター情報を追加してください。</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $originalCharacters->links() }}
</div>

@include('writer.original_characters._layout_end')
