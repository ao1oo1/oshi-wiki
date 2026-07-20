<?php

namespace App\Services;

use App\Models\WorkMonetizationLink;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

class LinkVerificationService
{
    private const TIMEOUT_SECONDS = 12;
    private const CONNECT_TIMEOUT_SECONDS = 5;

    public function __construct(
        private readonly AffiliateUrlBuilderService $urlBuilder
    ) {
    }

    public function verify(
        WorkMonetizationLink $link,
        string $method = 'manual'
    ): array {
        $link->loadMissing(['service', 'affiliateProgram']);

        try {
            $url = $this->urlBuilder->build($link);
        } catch (ValidationException $exception) {
            return $this->saveResult(
                $link,
                'unknown',
                $method,
                'URL生成エラー：' . $this->firstValidationMessage($exception)
            );
        }

        try {
            $response = $this->request('head', $url);

            if (in_array($response->status(), [405, 501], true)) {
                $response = $this->request('get', $url);
            }

            return $this->saveHttpResult(
                $link,
                $response,
                $method
            );
        } catch (ConnectionException $exception) {
            return $this->saveResult(
                $link,
                'checking',
                $method,
                '接続エラー：' . mb_substr($exception->getMessage(), 0, 1000)
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->saveResult(
                $link,
                'checking',
                $method,
                '検証処理エラー：' . mb_substr($exception->getMessage(), 0, 1000)
            );
        }
    }

    public function verifyActiveLinks(
        string $method = 'scheduled',
        ?int $limit = null
    ): array {
        $query = WorkMonetizationLink::query()
            ->with(['service', 'affiliateProgram'])
            ->where('is_active', true)
            ->whereIn(
                'availability_status',
                ['available', 'checking', 'unknown']
            )
            ->orderByRaw('last_verified_at IS NULL DESC')
            ->orderBy('last_verified_at')
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit(max(1, $limit));
        }

        $summary = [
            'total' => 0,
            'available' => 0,
            'checking' => 0,
            'unknown' => 0,
            'ended' => 0,
        ];

        $query->chunkById(50, function ($links) use (&$summary, $method): void {
            foreach ($links as $link) {
                $result = $this->verify($link, $method);
                $summary['total']++;
                $summary[$result['status']]++;
            }
        });

        return $summary;
    }

    private function request(string $method, string $url): Response
    {
        $request = Http::accept('*/*')
            ->withUserAgent('Oshi-Wiki-LinkVerifier/1.0')
            ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->timeout(self::TIMEOUT_SECONDS)
            ->retry(
                1,
                250,
                throw: false
            );

        return $method === 'head'
            ? $request->head($url)
            : $request->get($url);
    }

    private function saveHttpResult(
        WorkMonetizationLink $link,
        Response $response,
        string $method
    ): array {
        $statusCode = $response->status();

        if ($response->successful() || $response->redirect()) {
            return $this->saveResult(
                $link,
                'available',
                $method,
                "HTTP {$statusCode}：リンク先を確認できました。"
            );
        }

        if (in_array($statusCode, [404, 410], true)) {
            return $this->saveResult(
                $link,
                'ended',
                $method,
                "HTTP {$statusCode}：リンク先が見つからないか、提供が終了しています。"
            );
        }

        return $this->saveResult(
            $link,
            'checking',
            $method,
            "HTTP {$statusCode}：一時的なエラーまたはアクセス制限の可能性があります。"
        );
    }

    private function saveResult(
        WorkMonetizationLink $link,
        string $status,
        string $method,
        string $note
    ): array {
        $link->forceFill([
            'availability_status' => $status,
            'last_verified_at' => now(),
            'verification_method' => $method,
            'verification_note' => $note,
        ])->save();

        return [
            'status' => $status,
            'note' => $note,
        ];
    }

    private function firstValidationMessage(
        ValidationException $exception
    ): string {
        return collect($exception->errors())
            ->flatten()
            ->first()
            ?: 'URLを生成できませんでした。';
    }
}
