@props([
    'position' => 'top',
])

@php
    $isTop = $position === 'top';
    $targetId = $isTop
        ? 'admin-page-bottom'
        : 'admin-page-top';
    $label = $isTop
        ? '最下部へ'
        : '上部へ';
    $arrow = $isTop
        ? '↓'
        : '↑';
@endphp

<nav
    class="admin-page-jump admin-page-jump-{{ $position }}"
    aria-label="{{ $isTop ? 'ページ下部への移動' : 'ページ上部への移動' }}"
>
    <a
        href="#{{ $targetId }}"
        class="admin-page-jump-link"
        data-admin-page-jump
    >
        <span aria-hidden="true">{{ $arrow }}</span>
        <span>{{ $label }}</span>
    </a>
</nav>
