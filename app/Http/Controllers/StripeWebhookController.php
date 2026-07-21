<?php

namespace App\Http\Controllers;

use App\Models\BillingWebhookEvent;
use App\Services\StripeWebhookService;
use App\Services\StripeWebhookSignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StripeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        StripeWebhookSignatureService $signature,
        StripeWebhookService $service
    ): JsonResponse {
        $payload = $request->getContent();

        try {
            $signature->verify(
                $payload,
                (string) $request->header('Stripe-Signature')
            );
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Invalid signature.',
            ], 400);
        }

        $event = json_decode(
            $payload,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $eventId = (string) ($event['id'] ?? '');
        $eventType = (string) ($event['type'] ?? '');

        if ($eventId === '' || $eventType === '') {
            return response()->json([
                'message' => 'Invalid event.',
            ], 400);
        }

        $record = BillingWebhookEvent::query()->firstOrCreate(
            ['provider_event_id' => $eventId],
            [
                'provider' => 'stripe',
                'event_type' => $eventType,
                'status' => 'received',
            ]
        );

        if ($record->status === 'processed') {
            return response()->json(['received' => true]);
        }

        try {
            $service->handle($event);

            $record->update([
                'status' => 'processed',
                'processed_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $record->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    2000
                ),
            ]);

            report($exception);

            return response()->json([
                'message' => 'Webhook processing failed.',
            ], 500);
        }

        return response()->json(['received' => true]);
    }
}
