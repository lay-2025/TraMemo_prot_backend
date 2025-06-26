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

以上