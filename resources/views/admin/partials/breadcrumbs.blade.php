@php
    $adminBreadcrumbItems = \App\Support\AdminBreadcrumbs::items();
@endphp

@if ($adminBreadcrumbItems !== [])
    <nav
        aria-label="パンくずリスト"
        class="min-w-0 flex-1"
        data-admin-breadcrumbs
    >
        <ol class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-600">
            @foreach ($adminBreadcrumbItems as $item)
                <li class="flex min-w-0 items-center gap-2">
                    @if (! $loop->first)
                        <span aria-hidden="true" class="shrink-0 text-slate-400">＞</span>
                    @endif

                    @if ($item['url'])
                        <a
                            href="{{ $item['url'] }}"
                            class="break-words font-medium text-slate-700 underline-offset-4 hover:text-slate-950 hover:underline"
                        >
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span
                            @if ($loop->last) aria-current="page" @endif
                            class="break-words font-semibold text-slate-900"
                        >
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
