<div data-term-usage-row class="rounded-xl border border-gray-200 bg-gray-50 p-4">
    <div class="mb-3 flex items-center justify-between gap-3">
        <p class="font-bold">用語</p>
        <button type="button" data-remove-row class="text-sm font-bold text-red-600 hover:underline">削除</button>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label class="oshi-label">用語</label>
            <input type="text" name="term_usages[{{ $index }}][term]" value="{{ $term['term'] ?? '' }}" class="oshi-input" placeholder="例：マジフト">
        </div>
        <div>
            <label class="oshi-label">意味</label>
            <textarea name="term_usages[{{ $index }}][meaning]" rows="3" class="oshi-input" placeholder="用語の意味">{{ $term['meaning'] ?? '' }}</textarea>
        </div>
        <div>
            <label class="oshi-label">作中での使用例</label>
            <textarea name="term_usages[{{ $index }}][usage_example]" rows="3" class="oshi-input" placeholder="例：放課後、マジフト部の練習へ向かう。">{{ $term['usage_example'] ?? '' }}</textarea>
        </div>
    </div>
</div>
