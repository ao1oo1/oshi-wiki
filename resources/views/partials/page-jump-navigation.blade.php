@props([
    'position' => 'top',
])

@php
    $isTop = $position === 'top';
    $targetId = $isTop
        ? 'page-bottom'
        : 'page-top';
    $label = $isTop
        ? '最下部へ'
        : '最上部へ';
    $arrow = $isTop
        ? '↓'
        : '↑';
@endphp

<nav
    class="page-jump page-jump-{{ $position }}"
    aria-label="{{ $isTop ? 'ページ最下部への移動' : 'ページ最上部への移動' }}"
>
    <a
        href="#{{ $targetId }}"
        class="page-jump-link {{ $isTop
            ? 'page-jump-link-bottom'
            : 'page-jump-link-top' }}"
        data-page-jump
    >
        <span
            class="page-jump-icon"
            aria-hidden="true"
        >{{ $arrow }}</span>
        <span class="page-jump-label">{{ $label }}</span>
    </a>
</nav>
