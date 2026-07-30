@php
    $googleAnalyticsMeasurementId = 'G-ESQQTQB1QH';

    $googleAnalyticsExcluded = request()->routeIs(
        'admin.*',
        'writer.*'
    );
@endphp

@if (
    app()->environment('production')
    && ! $googleAnalyticsExcluded
)
    <!-- Google tag (gtag.js) -->
    <script
        async
        src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsMeasurementId }}"
    ></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', '{{ $googleAnalyticsMeasurementId }}');
    </script>
@endif
