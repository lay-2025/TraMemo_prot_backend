<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Svix\Webhook;
use Svix\Exception\WebhookVerificationException;
use App\Application\User\UseCases\RegisterUserUseCase;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleClerk(Request $request, RegisterUserUseCase $registerUserUseCase)
    {
        $clerkSigningSecret = env('CLERK_WEBHOOK_SIGNING_SECRET');
        $payload = $request->getContent();
        $headers = collect($request->headers->all())->transform(function ($item) {
            return $item[0];
        });

        try {
            // SvixSDKを利用してClerkのWebhook署名検証
            $wh = new Webhook($clerkSigningSecret);
            $json = $wh->verify($payload, $headers);

            info('Clerk webhook received', [
                'payload' => $payload,
                'headers' => $request->headers->all(),
            ]);

            // JSONデコード
            $payloadArray = json_decode($payload, true);

            // Clerkのユーザー作成イベント
            // ユーザ編集・削除もここから分岐させる予定
            if (($payloadArray['type'] ?? '') === 'user.created') {
                $userData = $payloadArray['data'];
                $registerUserUseCase->handle($userData);

                Log::notice('Clerk webhook processed successfully', [
                    'event_type' => $payloadArray['type'] ?? 'unknown',
                    'clerk_user_id' => $payloadArray['data']['id'] ?? null,
                ]);
            }

            return response()->noContent();
        } catch (WebhookVerificationException $e) {
            Log::alert('Clerk webhook verification failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
                'headers' => $request->headers->all(),
            ]);
            return response(null, 400);
        }
    }
}
