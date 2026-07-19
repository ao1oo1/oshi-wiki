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
        class="page-jump-link"
        data-page-jump
    >
        <span aria-hidden="true">{{ $arrow }}</span>
        <span>{{ $label }}</span>
    </a>
</nav>
