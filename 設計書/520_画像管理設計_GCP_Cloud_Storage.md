# 520_画像管理設計_GCP Cloud Storage

## 1. 目的・概要
本ドキュメントは、TraMemoプロジェクトにおける画像管理方式としてGoogle Cloud Platform (GCP)のCloud Storageを採用した場合の設計・運用・実装指針をまとめたものです。インフラ構成、API設計、セキュリティ、運用・監視、コスト最適化までを体系的に記載します。

---

## 2. インフラ構成
- **ストレージ**: Google Cloud Storage（GCS）
- **CDN**: Cloud CDN
- **画像処理**: Cloud Functions + ImageMagick
- **認証**: Clerk（またはFirebase Auth）

### 2.1 サービス構成図
```
[フロントエンド] → [Laravel API] → [Google Cloud Storage]
                              ↓
[Cloud CDN] ← [Cloud Functions] ← [画像処理]
```

---

## 3. バックエンド（Laravel）設計
### 3.1 必要パッケージ
- google/cloud-storage
- intervention/image

### 3.2 ファイルシステム・環境変数設定
- GCS用ディスク設定（config/filesystems.php）
- サービスアカウントキー配置
- .envに各種GCP設定

### 3.3 サービスアカウント・権限
- Cloud Storage権限付与
- JSONキーを安全に管理

---

## 4. DB設計
- photosテーブル：GCS上のファイルパス、サムネイル、ファイルサイズ、MIME、公開フラグ等を管理
- travels, usersテーブルとリレーション

---

## 5. API設計
### 5.1 画像アップロード
- `POST /api/photos`（multipart/form-data, Clerk認証必須）
- パラメータ：image, travel_id, caption, is_public
- バリデーション：JPEG, PNG, WebP, 最大5MB

### 5.2 画像取得
- `GET /api/photos/{id}`（公開画像は認証不要）

### 5.3 画像削除
- `DELETE /api/photos/{id}`（所有者/管理者のみ）

### 5.4 画像一覧取得
- `GET /api/travels/{travel_id}/photos`

### 5.5 署名付きURL発行
- `POST /api/photos/presign`

---

## 6. 画像処理・サムネイル生成
- Cloud Functions + ImageMagickで自動サムネイル生成
- サムネイルは`/thumbnails/`配下に保存
- 画像圧縮・WebP変換も可能

---

## 7. データ処理フロー（分離型）

### 7.1 データ処理の流れ

#### ① 画像アップロード（旅行記録作成前）
1. ユーザーが画像を選択し、画像アップロードAPI（POST /api/photos）を呼ぶ
2. バックエンドは画像をクラウドストレージに保存し、photosテーブルにレコードを作成
    - travel_idはNULL（未紐付）
    - user_id, file_path, created_at等を保存
3. APIは画像IDやURLを返す
4. フロントエンドは画像のプレビューや一時的な画像リストを保持

#### ② 旅行記録作成
1. ユーザーが旅行記録の入力を完了し、作成API（POST /api/travels）を呼ぶ
    - リクエストボディに「画像IDの配列」または「画像URLの配列」を含める（例: photo_ids: [101, 102]）
2. バックエンドはtravelレコードを新規作成
3. photosテーブルの該当レコードのtravel_idを、作成したtravelのIDに更新（UPDATE）
4. レスポンスで旅行記録と紐付いた画像情報を返す

#### ③ 未紐付画像のクリーンアップ
- 旅行記録作成がキャンセルされた場合や、アップロード後に放置された画像は、定期的なバッチ等で削除（travel_idがNULLかつ一定期間経過したもの）

### 7.2 DB設計イメージ

| id  | travel_id | user_id | file_path | ... | created_at |
|-----|-----------|---------|-----------|-----|------------|
|101  | NULL      | 1       | ...       | ... | ...        | ←アップロード直後
|101  | 55        | 1       | ...       | ... | ...        | ←旅行記録作成後

### 7.3 運用ポイント
- 旅行記録作成時に画像IDを指定しない場合は未紐付のまま（後で削除対象）
- 複数ユーザーが同時にアップロードしてもuser_idで管理できる
- 画像アップロード後に「どの旅行記録にも使わなかった」場合のクリーンアップ設計が重要

---


## 8. セキュリティ設計
- バケットアクセス制御（公開/非公開、署名付きURL）
- ファイルサイズ・拡張子制限
- ウイルススキャン（必要に応じて）
- CORS設定
- アップロード/ダウンロードの有効期限管理

---

## 9. 運用・監視
- Cloud Monitoringでストレージ使用量・API呼び出し・エラー率を監視
- バケットのバージョニング・バックアップ
- ログ設定（gcsチャンネル、slack通知等）

---

## 10. コスト最適化
- 画像の自動圧縮・WebP化
- 不要ファイルの定期削除・ライフサイクルポリシー
- CDNキャッシュ・ストレージクラス最適化

---

## 11. 実装例
- PhotoController, UploadPhotoUseCase, GCSImageService等のサンプルコード
- React側のアップロードコンポーネント例

---

## 12. GCP設定手順（抜粋）
- プロジェクト・バケット作成、サービスアカウント発行
- Cloud CDN・Cloud Functions有効化
- CORS・バケット権限・バージョニング設定

---

## 13. 参考リンク
- [Google Cloud Storage公式ドキュメント](https://cloud.google.com/storage/docs)
- [Cloud CDN公式ドキュメント](https://cloud.google.com/cdn/docs)
- [Laravel Google Cloud Storage](https://github.com/spatie/laravel-google-cloud-storage)
- [Intervention Image](https://image.intervention.io/)

---

*（最終更新日: 2024-07-xx）* 