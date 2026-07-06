@php
    $contributor = $contributor ?? null;
@endphp

@if ($contributor)
    <div class="oshi-card mt-6">
        <p class="oshi-muted mb-2">情報入力者</p>

        <a href="{{ route('public.staff.show', $contributor->staff_public_id) }}" style="display:flex;align-items:center;gap:12px;text-decoration:none;">
            @if ($contributor->profile_icon_path)
                <img
                    src="{{ asset('storage/' . $contributor->profile_icon_path) }}"
                    alt="{{ $contributor->displayName() }}"
                    style="width:52px;height:52px;border-radius:999px;object-fit:cover;"
                >
            @else
                <div style="width:52px;height:52px;border-radius:999px;background:#FED7E2;display:flex;align-items:center;justify-content:center;font-weight:700;color:#2D3748;">
                    {{ mb_substr($contributor->displayName(), 0, 1) }}
                </div>
            @endif

            <div>
                <strong>{{ $contributor->displayName() }}</strong>
                <p class="oshi-muted text-sm">プロフィールを見る</p>
            </div>
        </a>
    </div>
@endif
