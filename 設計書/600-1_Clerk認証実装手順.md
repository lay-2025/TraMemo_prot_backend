# Clerk認証 実装手順（React＋Laravel）

---

## 1. Clerkプロジェクト作成・初期設定

1. [Clerk公式サイト](https://clerk.com/)でアカウント作成
2. 新規プロジェクトを作成し、**フロントエンド用APIキー**と**バックエンド用APIキー**を取得
3. 必要な認証方式（メール、Googleなど）をClerkダッシュボードで有効化

---

## 2. フロントエンド（React）実装

1. Clerk React SDKのインストール  
   ```
   npm install @clerk/clerk-react
   ```

2. ClerkProviderでアプリ全体をラップ  
   ```tsx
   // index.tsx
   import { ClerkProvider } from '@clerk/clerk-react';

   <ClerkProvider publishableKey="YOUR_CLERK_PUBLISHABLE_KEY">
     <App />
   </ClerkProvider>
   ```

3. サインイン/サインアップ画面の設置  
   ```tsx
   import { SignIn, SignUp } from '@clerk/clerk-react';

   // ルーティングに応じて
   <SignIn />
   <SignUp />
   ```

4. サインイン後のユーザー情報取得・APIリクエスト時のJWT付与  
   ```tsx
   import { useAuth } from '@clerk/clerk-react';

   const { getToken } = useAuth();
   // APIリクエスト時
   const token = await getToken();
   fetch('/api/endpoint', {
     headers: { Authorization: `Bearer ${token}` }
   });
   ```

---

## 3. バックエンド（Laravel）実装

1. JWT検証用ライブラリの導入（例: firebase/php-jwt など）
   ```
   composer require firebase/php-jwt
   ```

2. Clerkの公開鍵取得・JWT検証ミドルウェア作成  
   - Clerk公式ドキュメントの[JWT検証ガイド](https://clerk.com/docs/backend-requests)を参照
   - AuthorizationヘッダーからJWTを取得し、Clerkの公開鍵で検証
   - JWTの`sub`（ClerkユーザーID）を取得

3. usersテーブルに`clerk_user_id`カラムを追加し、ユーザー情報を管理  
   - 初回ログイン時は`clerk_user_id`で新規登録
   - 2回目以降は`clerk_user_id`でユーザーを特定

4. APIルートにJWT検証ミドルウェアを適用  
   ```php
   // routes/api.php
   Route::middleware(['clerk.jwt'])->group(function () {
       Route::get('/user', [UserController::class, 'show']);
       // 他の認証必須API
   });
   ```

---

## 4. 動作確認

1. Reactアプリでサインイン・サインアップができることを確認
2. サインイン後、APIリクエスト時にJWTが付与されていることを確認
3. Laravel側でJWTが正しく検証され、ユーザー情報が取得・登録できることを確認

---

## 5. 補足

- Clerkの管理画面で認証方式やメールテンプレートのカスタマイズが可能
- JWTの有効期限やリフレッシュの仕組みもClerkが自動管理
- バックエンドでユーザー情報を拡張したい場合は、`clerk_user_id`をキーに独自情報を追加

---

## 6. authorizedParties に入れるべきデータについて

### authorizedParties とは

`authorizedParties` は、Clerkの認証トークン（JWT）の `azp`（Authorized Party）クレームと一致する値を指定するための設定です。  
これは「どのクライアント（アプリケーション）がこのトークンを使うことを許可されているか」を示します。

### 何を入れるべきか

- 通常は **フロントエンドのURL** や **ClerkのクライアントID** を指定します。
- Clerkが発行するトークンの `azp` クレーム値と一致させる必要があります。

#### 例1: ローカル開発環境の場合

```php
authorizedParties: ['http://localhost:3000']
```

#### 例2: 本番環境の場合

```php
authorizedParties: ['https://your-production-domain.com']
```

#### 例3: ClerkのクライアントIDを指定する場合

```php
authorizedParties: ['clerk.apps.xxxxx']
```
※ ClerkのクライアントIDはClerkダッシュボードで確認できます。

### どの値を指定すればよいか分からない場合

1. フロントエンドで取得したJWT（セッショントークン）を[JWTデコーダー](https://jwt.io/)などでデコードし、`azp` クレームの値を確認してください。
2. その値を `authorizedParties` に指定してください。

---

**まとめ**  
- `authorizedParties` には、トークンの `azp` クレーム値と一致する値を指定します。
- 通常はフロントエンドのURLやクライアントIDです。
- ローカル・本番で値が異なる場合があるので、環境ごとに設定を切り替えてください。

---

## 7. Clerk Webhookを利用したユーザーデータのDB登録（ローカル環境例）

### 概要

ClerkのWebhook機能を使うことで、フロントエンドでユーザー登録が行われた際に、  
LaravelバックエンドのDBへユーザーデータを自動登録できます。

---

### 1. LaravelでWebhook受信用エンドポイントを作成

#### ルート追加

```php
// routes/api.php
Route::post('/webhook/clerk', [\App\Http\Controllers\WebhookController::class, 'handleClerk']);
```

#### コントローラ作成例

```php
// app/Http/Controllers/WebhookController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class WebhookController extends Controller
{
    public function handleClerk(Request $request)
    {
        $payload = $request->all();

        // Clerkのユーザー作成イベント
        if (($payload['type'] ?? '') === 'user.created') {
            $userData = $payload['data'];
            User::updateOrCreate(
                ['clerk_user_id' => $userData['id']],
                [
                    'email' => $userData['email_addresses'][0]['email_address'] ?? null,
                    'name' => $userData['first_name'] ?? '',
                    // 必要に応じて他の項目も
                ]
            );
        }

        return response()->json(['status' => 'ok']);
    }
}
```

---

### 2. ClerkダッシュボードでWebhook URLを設定

- ローカル開発の場合は [ngrok](https://ngrok.com/) などで一時的に外部公開し、  
  例: `https://xxxx.ngrok.io/api/webhook/clerk` をClerkのWebhook設定画面に登録します。

---

### 3. セキュリティ対策（推奨）

- ClerkのWebhook署名検証を実装し、不正リクエストを防ぐ  
  [Clerk公式Webhook署名検証ガイド](https://clerk.com/docs/webhooks/verify-signature) を参照

---

### 4. 動作確認

1. フロントエンドで新規ユーザー登録を実施
2. ClerkからWebhookが送信され、Laravelのエンドポイントが呼ばれる
3. usersテーブルに新しいユーザーが登録されていることを確認

---

**ポイント**
- ClerkのWebhookは「user.created」などのイベントごとに送信されます。
- ローカル開発時はngrok等で一時的に外部公開する必要があります。
- 本番環境ではHTTPSのエンドポイントを直接指定してください。

---

以上