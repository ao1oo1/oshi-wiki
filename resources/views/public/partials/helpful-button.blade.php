<div class="my-6 rounded bg-white p-4 shadow">
    @if (session('status'))
        <p class="mb-3 rounded bg-green-50 px-3 py-2 text-sm text-green-700">
            {{ session('status') }}
        </p>
    @endif

    <form method="POST" action="{{ route('public.helpful.store') }}">
        @csrf
        <input type="hidden" name="target_type" value="{{ $targetType }}">
        <input type="hidden" name="target_id" value="{{ $targetId }}">

        <button
            type="submit"
            class="rounded bg-pink-500 px-4 py-2 font-semibold text-white hover:bg-pink-600"
        >
            役に立った
            <span class="ml-1">({{ $helpfulCount ?? 0 }})</span>
        </button>
    </form>
</div>
