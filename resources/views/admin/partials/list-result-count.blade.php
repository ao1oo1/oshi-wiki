<div
    class="mb-3 flex w-full justify-end px-1"
    data-admin-result-count
>
    <p class="text-right text-sm font-semibold text-[#4A5568]">
        検索結果
        <span class="text-base font-bold text-[#2D3748]">
            {{ number_format($items->total()) }}
        </span>件
        <span class="mx-1 text-[#A0AEC0]">／</span>
        全体
        <span class="text-base font-bold text-[#2D3748]">
            {{ number_format($totalCount) }}
        </span>件
    </p>
</div>
