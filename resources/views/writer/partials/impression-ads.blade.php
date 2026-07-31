@inject('impressionAdSlots', 'App\Services\ImpressionAdSlotService')

@php
    $adSlots = $impressionAdSlots->displayableForWriter(
        request(),
        $position
    );
@endphp

@if ($adSlots->isNotEmpty())
    <aside
        class="writer-impression-ads writer-impression-ads--{{ $position }}"
        aria-label="広告"
        data-writer-ad-position="{{ $position }}"
    >
        @foreach ($adSlots as $adSlot)
            <div
                class="writer-impression-ad
                    @if ($adSlot->device_type === 'desktop')
                        writer-impression-ad--desktop
                    @elseif ($adSlot->device_type === 'mobile')
                        writer-impression-ad--mobile
                    @endif"
                data-ad-slot-id="{{ $adSlot->id }}"
                data-ad-service="{{ $adSlot->service->slug }}"
            >
                <p class="writer-impression-ad__label">広告</p>
                {!! $adSlot->service->impression_script !!}
            </div>
        @endforeach
    </aside>
@endif
