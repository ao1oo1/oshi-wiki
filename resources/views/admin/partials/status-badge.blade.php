@php
    $statusLabel = match ($status ?? 'draft') {
        'published' => '公開',
        'private' => '非公開',
        default => '下書き',
    };

    $statusStyle = match ($status ?? 'draft') {
        'published' => 'background:#dcfce7;color:#166534;',
        'private' => 'background:#fee2e2;color:#991b1b;',
        default => 'background:#fef3c7;color:#92400e;',
    };
@endphp

<span
    class="inline-block rounded px-2 py-1 text-xs font-bold"
    style="{{ $statusStyle }}"
>
    {{ $statusLabel }}
</span>
