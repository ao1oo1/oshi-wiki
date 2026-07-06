@if (! empty($contributor))
    <div class="my-6 rounded bg-white p-4 shadow">
        <p class="mb-2 text-sm font-semibold text-gray-500">登録スタッフ</p>

        <a href="{{ route('public.staff.show', $contributor->staff_public_id) }}" class="flex items-center gap-3">
            @if ($contributor->profile_icon_path)
                <img
                    src="{{ asset('storage/' . $contributor->profile_icon_path) }}"
                    alt="{{ $contributor->public_username ?: $contributor->name }}"
                    class="h-12 w-12 rounded-full object-cover"
                >
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-200 font-bold text-gray-500">
                    {{ mb_substr($contributor->public_username ?: $contributor->name, 0, 1) }}
                </div>
            @endif

            <div>
                <p class="font-semibold text-gray-900">
                    {{ $contributor->public_username ?: $contributor->name }}
                </p>
                <p class="text-xs text-gray-500">{{ $contributor->staff_public_id }}</p>
            </div>
        </a>
    </div>
@endif
