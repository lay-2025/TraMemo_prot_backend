<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends \App\Http\Controllers\Controller
{
    // 1. アプリ起動確認
    public function app()
    {
        return response()->json(['app' => 'ok']);
    }

    // 2. DB接続確認
    public function db()
    {
        try {
            DB::connection()->getPdo();
            return response()->json(['db' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['db' => 'ng', 'error' => $e->getMessage()], 500);
        }
    }

    // 3. ストレージ接続確認（今はスキップ、将来追加）
    // public function storage()
    // {
    //     try {
    //         Storage::disk('local')->exists('/');
    //         return response()->json(['storage' => 'ok']);
    //     } catch (\Exception $e) {
    //         return response()->json(['storage' => 'ng', 'error' => $e->getMessage()], 500);
    //     }
    // }

    // 4. 環境変数・設定値の存在確認（本番環境が安定したら削除）
    public function env()
    {
        $required_env = ['APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
        $missing = [];
        foreach ($required_env as $env) {
            if (empty(env($env))) {
                $missing[] = $env;
            }
        }
        if (empty($missing)) {
            return response()->json(['env' => 'ok']);
        } else {
            return response()->json(['env' => 'ng', 'missing' => $missing], 500);
        }
    }

    // 5. 認証確認
    public function auth(Request $request)
    {
        $clerkUserId = $request->attributes->get('clerk_user_id');
        if ($clerkUserId) {
            return response()->json(['auth' => 'ok', 'clerk_user_id' => $clerkUserId]);
        } else {
            return response()->json(['auth' => 'ng'], 401);
        }
    }

    // 6. バージョン情報の返却
    public function version()
    {
        // APP_VERSION環境変数→config/app.php→unknown の順で取得
        $version = env('APP_VERSION') ?? config('app.version') ?? 'unknown';
        return response()->json(['version' => $version]);
    }
}
