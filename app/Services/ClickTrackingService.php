<?php

namespace App\Services;

use App\Models\LinkClick;
use App\Models\WorkMonetizationLink;
use Illuminate\Http\Request;

class ClickTrackingService
{
    private const DUPLICATE_SECONDS = 10;

    public function record(
        WorkMonetizationLink $link,
        Request $request
    ): void {
        $visitorHash = $this->hashValue(
            implode('|', [
                now()->format('Y-m-d'),
                (string) $request->ip(),
            ])
        );

        $userAgent = trim((string) $request->userAgent());
        $userAgentHash = $userAgent !== ''
            ? $this->hashValue($userAgent)
            : null;

        $duplicate = LinkClick::query()
            ->where('work_monetization_link_id', $link->id)
            ->where('visitor_hash', $visitorHash)
            ->where('user_agent_hash', $userAgentHash)
            ->where(
                'clicked_at',
                '>=',
                now()->subSeconds(self::DUPLICATE_SECONDS)
            )
            ->exists();

        if ($duplicate) {
            return;
        }

        [$refererHost, $refererPath] =
            $this->sanitizeReferer($request->headers->get('referer'));

        LinkClick::query()->create([
            'work_monetization_link_id' => $link->id,
            'work_id' => $link->work_id,
            'service_id' => $link->service_id,
            'affiliate_program_id' => $link->affiliate_program_id,
            'visitor_hash' => $visitorHash,
            'user_agent_hash' => $userAgentHash,
            'referer_host' => $refererHost,
            'referer_path' => $refererPath,
            'clicked_at' => now(),
        ]);
    }

    private function hashValue(string $value): string
    {
        return hash_hmac(
            'sha256',
            $value,
            (string) config('app.key')
        );
    }

    private function sanitizeReferer(?string $referer): array
    {
        if (! filled($referer)) {
            return [null, null];
        }

        $parts = parse_url($referer);

        if (! is_array($parts)) {
            return [null, null];
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        return [
            $host !== '' ? mb_substr($host, 0, 255) : null,
            $path !== '' ? mb_substr($path, 0, 1000) : null,
        ];
    }
}
