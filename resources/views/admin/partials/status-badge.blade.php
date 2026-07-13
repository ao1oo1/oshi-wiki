@php
    $status = $status ?? 'draft';

    $normalizedStatus = match ((string) $status) {
        'published', 'publish', '公開' => 'published',
        'private', '非公開' => 'private',
        'draft', '下書き' => 'draft',
        default => (string) $status,
    };

    $label = match ($normalizedStatus) {
        'published' => '公開',
        'private' => '非公開',
        'draft' => '下書き',
        default => $status ?: '未設定',
    };

    $style = match ($normalizedStatus) {
        'published' => 'background-color: #D1FAE5; color: #047857;',
        'private' => 'background-color: #E2E8F0; color: #4A5568;',
        'draft' => 'background-color: #FEF3C7; color: #92400E;',
        default => 'background-color: #EDF2F7; color: #4A5568;',
    };
@endphp

<span
    class="inline-flex items-center justify-center rounded-lg px-3 py-1 text-sm font-bold leading-none whitespace-nowrap"
    style="{{ $style }}"
>
    {{ $label }}
</span>
