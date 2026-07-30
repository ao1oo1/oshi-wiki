@inject('impressionAdSlots', 'App\Services\ImpressionAdSlotService')

@php
    $adSlots = $impressionAdSlots->displayableFor(
        request(),
        $position
    );
@endphp

@if ($adSlots->isNotEmpty())
    <aside
        class="public-impression-ads public-impression-ads--{{ $position }}"
        aria-label="広告"
    >
        @foreach ($adSlots as $adSlot)
            <div
                class="public-impression-ad
                    @if ($adSlot->device_type === 'desktop')
                        public-impression-ad--desktop
                    @elseif ($adSlot->device_type === 'mobile')
                        public-impression-ad--mobile
                    @endif"
                data-ad-slot-id="{{ $adSlot->id }}"
                data-ad-service="{{ $adSlot->service->slug }}"
            >
                <p class="public-impression-ad__label">広告</p>
                {!! $adSlot->service->impression_script !!}
            </div>
        @endforeach
    </aside>
@endif
