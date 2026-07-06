<form method="POST" action="{{ route('public.helpful.store') }}" class="mt-4">
    @csrf
    <input type="hidden" name="target_type" value="{{ $targetType }}">
    <input type="hidden" name="target_id" value="{{ $target->id }}">

    <button type="submit" class="oshi-btn oshi-btn-sub">
        役に立った
        <span>（{{ $target->helpful_count ?? 0 }}）</span>
    </button>
</form>
