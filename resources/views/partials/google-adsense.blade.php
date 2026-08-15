@php
    $adsenseEnabled = true;
    $adsensePublisherId = 'ca-pub-3916030283806562';

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('adsense_settings')) {
            $adsenseSetting = \App\Models\AdsenseSetting::query()
                ->first();

            if ($adsenseSetting !== null) {
                $adsenseEnabled = (bool) $adsenseSetting->is_enabled;
                $adsensePublisherId = trim(
                    (string) $adsenseSetting->publisher_id
                );
            }
        }
    } catch (\Throwable $exception) {
        // Migration前やDB障害時は従来のAdSense設定を維持する。
    }
@endphp

@if ($adsenseEnabled === true && $adsensePublisherId !== '')
    <script
        async
        src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsensePublisherId }}"
        crossorigin="anonymous"
    ></script>
@endif
