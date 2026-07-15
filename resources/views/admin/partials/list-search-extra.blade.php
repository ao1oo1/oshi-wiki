<div class="admin-index-filter-field">
    <label for="status" class="mb-1 block font-medium">状態</label>
    <select id="status" name="status" class="rounded border-gray-300">
        <option value="">すべての状態</option>
        <option value="published" @selected(($selectedStatus ?? '') === 'published')>公開</option>
        <option value="draft" @selected(($selectedStatus ?? '') === 'draft')>下書き</option>
        <option value="private" @selected(($selectedStatus ?? '') === 'private')>非公開</option>
    </select>
</div>
<div class="admin-index-filter-field">
    <label for="exact_keyword" class="mb-1 block font-medium">キーワード（完全一致）</label>
    <input id="exact_keyword" type="text" name="exact_keyword"
           value="{{ $exactKeyword ?? '' }}"
           class="rounded border-gray-300"
           placeholder="名称などを完全一致で検索">
</div>
