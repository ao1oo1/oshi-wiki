@include('writer.original_characters._layout_start', ['title' => '関係性'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">関係性</h2>
</div>

<div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <div>
        <p class="text-lg font-bold text-[#2D3748]">登録済みの関係性</p>
        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">
            登録数：{{ $count }} / {{ $limit === null ? '制限なし' : $limit }}
        </p>
    </div>

    @if ($limit === null || $count < $limit)
        <a href="{{ route('writer.original-character-relationships.create') }}"
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
                <th class="px-6 py-4">キャラクター</th>
                <th class="px-6 py-4">相手</th>
                <th class="px-6 py-4">呼び方</th>
                <th class="px-6 py-4">関係性</th>
                <th class="px-6 py-4">状態</th>
                <th class="px-6 py-4">操作</th>
            </tr>
        </thead>
        <tbody class="text-[#2D3748]">
            @forelse ($relationships as $relationship)
                <tr class="border-t border-[#E2E8F0]">
                    <td class="px-6 py-5 text-lg font-bold">{{ $relationship->fromDisplayName() }}</td>
                    <td class="px-6 py-5 text-lg font-bold">{{ $relationship->toDisplayName() }}</td>
                    <td class="px-6 py-5">{{ $relationship->called_name ?: '-' }}</td>
                    <td class="px-6 py-5">{{ $relationship->relationship_type ?: '-' }}</td>
                    <td class="px-6 py-5">
                        <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                            {{ $relationship->status === 'active' ? '有効' : '下書き' }}
                        </span>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('writer.original-character-relationships.show', $relationship) }}"
                               class="rounded-xl border border-[#CBD5E0] px-4 py-2 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                                詳細
                            </a>
                            <a href="{{ route('writer.original-character-relationships.edit', $relationship) }}"
                               class="rounded-xl bg-[#FED7E2] px-4 py-2 font-bold text-[#2D3748] hover:opacity-90">
                                編集
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <p class="text-lg font-bold text-[#2D3748]">まだ関係性が登録されていません。</p>
                        <p class="mt-2 text-sm font-bold text-[#A0AEC0]">キャラクターを2人以上登録してから、関係性を追加してください。</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $relationships->links() }}
</div>

@include('writer.original_characters._layout_end')
