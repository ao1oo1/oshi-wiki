<div class="mb-6 rounded bg-white p-6 shadow">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:#2D3748;">
                {{ $title ?? '管理画面' }}
            </h1>

            @if (!empty($description))
                <p class="mt-2 text-gray-600">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if (!empty($backUrl))
            <a
                href="{{ $backUrl }}"
                style="display:inline-block;background:#A0AEC0;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
            >
                戻る
            </a>
        @endif
    </div>
</div>
