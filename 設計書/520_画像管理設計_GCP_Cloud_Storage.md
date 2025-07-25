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

## 7. セキュリティ設計
- バケットアクセス制御（公開/非公開、署名付きURL）
- ファイルサイズ・拡張子制限
- ウイルススキャン（必要に応じて）
- CORS設定
- アップロード/ダウンロードの有効期限管理

---

## 8. 運用・監視
- Cloud Monitoringでストレージ使用量・API呼び出し・エラー率を監視
- バケットのバージョニング・バックアップ
- ログ設定（gcsチャンネル、slack通知等）

---

## 9. コスト最適化
- 画像の自動圧縮・WebP化
- 不要ファイルの定期削除・ライフサイクルポリシー
- CDNキャッシュ・ストレージクラス最適化

---

## 10. 実装例
- PhotoController, UploadPhotoUseCase, GCSImageService等のサンプルコード
- React側のアップロードコンポーネント例

---

## 11. GCP設定手順（抜粋）
- プロジェクト・バケット作成、サービスアカウント発行
- Cloud CDN・Cloud Functions有効化
- CORS・バケット権限・バージョニング設定

---

## 12. 参考リンク
- [Google Cloud Storage公式ドキュメント](https://cloud.google.com/storage/docs)
- [Cloud CDN公式ドキュメント](https://cloud.google.com/cdn/docs)
- [Laravel Google Cloud Storage](https://github.com/spatie/laravel-google-cloud-storage)
- [Intervention Image](https://image.intervention.io/)

---

*（最終更新日: 2024-07-xx）* 