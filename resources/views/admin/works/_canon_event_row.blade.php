<div data-canon-event-row class="rounded-xl border border-gray-200 bg-gray-50 p-4">
    <div class="mb-3 flex items-center justify-between gap-3">
        <p class="font-bold">重要イベント</p>
        <button type="button" data-remove-row class="text-sm font-bold text-red-600 hover:underline">削除</button>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="oshi-label">時期・話数</label>
            <input type="text" name="canon_events[{{ $index }}][timing]" value="{{ $event['timing'] ?? '' }}" class="oshi-input" placeholder="例：第1章後、2年目の春">
        </div>
        <div>
            <label class="oshi-label">出来事</label>
            <input type="text" name="canon_events[{{ $index }}][event_name]" value="{{ $event['event_name'] ?? '' }}" class="oshi-input" placeholder="例：〇〇事件が発生">
        </div>
        <div>
            <label class="oshi-label">出来事の状態</label>
            <select name="canon_events[{{ $index }}][event_status]" class="oshi-input">
                <option value="">未設定</option>
                <option value="occurred" @selected(($event['event_status'] ?? '') === 'occurred')>すでに起きた</option>
                <option value="allowed" @selected(($event['event_status'] ?? '') === 'allowed')>触れてよい</option>
                <option value="not_yet" @selected(($event['event_status'] ?? '') === 'not_yet')>まだ起きていない</option>
                <option value="unknown" @selected(($event['event_status'] ?? '') === 'unknown')>時期不明</option>
            </select>
        </div>
        <div>
            <label class="oshi-label">補足</label>
            <textarea name="canon_events[{{ $index }}][notes]" rows="3" class="oshi-input" placeholder="出来事の内容や注意点">{{ $event['notes'] ?? '' }}</textarea>
        </div>
    </div>
</div>
