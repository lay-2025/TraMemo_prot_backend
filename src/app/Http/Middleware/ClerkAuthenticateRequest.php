<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Clerk\Backend\Helpers\Jwks\AuthenticateRequest;
use Clerk\Backend\Helpers\Jwks\AuthenticateRequestOptions;

class ClerkAuthenticateRequest
{
    public function handle(Request $request, Closure $next)
    {
        // ClerkのバックエンドAPIキーを.envから取得
        $clerkSecretKey = env('CLERK_SECRET_KEY');
        if (!$clerkSecretKey) {
            return response()->json(['message' => 'Clerk secret key not set'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // AuthenticateRequestOptionsを作成
        $options = new AuthenticateRequestOptions(
            secretKey: $clerkSecretKey,
            authorizedParties: [env('CLERK_AUTHORIZED_PARTY')],
        );

        // ClerkのAuthenticateRequestで認証
        $requestState = AuthenticateRequest::authenticateRequest($request, $options);

        if (!$requestState->isSignedIn()) {
            info('Clerk authentication failed', [
                'error' => $requestState->getErrorReason()?->getMessage() ?? 'Unknown error'
            ]);
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // 認証済みのClerkユーザーIDをリクエスト属性にセット
        $payload = $requestState->getPayload();
        $claims = json_decode(json_encode($payload), true); // stdClass to array
        $request->attributes->set('clerk_user_id', $claims['sub'] ?? null);

        // info($request->attributes->get('clerk_user_id') . ' authenticated successfully');

        return $next($request);
    }
}
