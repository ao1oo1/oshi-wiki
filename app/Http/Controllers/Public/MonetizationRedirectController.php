<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\WorkMonetizationLink;
use App\Services\ClickTrackingService;
use App\Services\LinkRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MonetizationRedirectController extends Controller
{
    public function __invoke(
        Request $request,
        string $publicKey,
        LinkRedirectService $redirectService,
        ClickTrackingService $trackingService
    ): RedirectResponse {
        $link = WorkMonetizationLink::query()
            ->where('public_key', $publicKey)
            ->firstOrFail();

        $url = $redirectService->resolve($link);

        $trackingService->record($link, $request);

        return redirect()->away($url, 302, [
            'Cache-Control' => 'no-store, private',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ]);
    }
}
