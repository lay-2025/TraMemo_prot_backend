# GCP画像管理設計書

## 1. 概要

本ドキュメントは、TraMemoプロジェクトにおける画像管理方式としてGoogle Cloud Platform (GCP)を採用した場合の設計・実装フローをまとめたものです。バックエンド（Laravel）、フロントエンド（React）双方の観点を含みます。

**採用サービス**:
- **ストレージ**: Google Cloud Storage
- **CDN**: Cloud CDN
- **画像処理**: Cloud Functions + ImageMagick
- **認証**: Firebase Auth（Clerk代替としても検討可能）

---

## 2. GCPサービス構成

### 2.1 採用サービス詳細

| サービス | 用途 | 料金（概算） |
|----------|------|-------------|
| Google Cloud Storage | 画像ファイル保存 | 月5GB無料、超過時$0.02/GB |
| Cloud CDN | 画像配信高速化 | 月1GB無料、超過時$0.08/GB |
| Cloud Functions | 画像処理自動化 | 月200万回無料 |
| Cloud Vision API | 画像解析・タグ付け | 月1000回無料 |

### 2.2 アーキテクチャ図

```
[フロントエンド] → [Laravel API] → [Google Cloud Storage]
                              ↓
[Cloud CDN] ← [Cloud Functions] ← [画像処理]
```

---

## 3. バックエンド（Laravel）設定

### 3.1 必要なパッケージ

```bash
composer require google/cloud-storage
composer require intervention/image
```

### 3.2 環境変数設定

```env
# GCP基本設定
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_KEY_FILE=storage/app/google-cloud-key.json
GOOGLE_CLOUD_STORAGE_BUCKET=traMemo-images
GOOGLE_CLOUD_STORAGE_URL=https://storage.googleapis.com/traMemo-images

# Cloud CDN設定
GOOGLE_CLOUD_CDN_URL=https://your-cdn-domain.com

# Cloud Functions設定
GOOGLE_CLOUD_FUNCTIONS_REGION=asia-northeast1
GOOGLE_CLOUD_FUNCTIONS_URL=https://asia-northeast1-your-project.cloudfunctions.net
```

### 3.3 ファイルシステム設定

```php
// config/filesystems.php
'gcs' => [
    'driver' => 'gcs',
    'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
    'key_file' => env('GOOGLE_CLOUD_KEY_FILE'),
    'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
    'url' => env('GOOGLE_CLOUD_STORAGE_URL'),
    'endpoint' => env('GOOGLE_CLOUD_STORAGE_ENDPOINT'),
    'use_path_style_endpoint' => env('GOOGLE_CLOUD_USE_PATH_STYLE_ENDPOINT', false),
],

'default' => env('FILESYSTEM_DISK', 'gcs'),
```

### 3.4 サービスアカウントキー設定

1. GCPコンソールでサービスアカウントを作成
2. Cloud Storage権限を付与
3. JSONキーをダウンロード
4. `storage/app/google-cloud-key.json`に配置

---

## 4. DB設計

### 4.1 photosテーブル

```sql
CREATE TABLE photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    travel_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    file_path TEXT NOT NULL COMMENT 'GCS上のファイルパス',
    thumbnail_path TEXT NULL COMMENT 'サムネイルパス',
    original_filename VARCHAR(255) NOT NULL COMMENT 'オリジナルファイル名',
    file_size INT UNSIGNED NOT NULL COMMENT 'ファイルサイズ（バイト）',
    mime_type VARCHAR(100) NOT NULL COMMENT 'MIMEタイプ',
    width INT UNSIGNED NULL COMMENT '画像幅',
    height INT UNSIGNED NULL COMMENT '画像高さ',
    caption TEXT NULL COMMENT 'キャプション',
    storage_type ENUM('gcs') DEFAULT 'gcs' NOT NULL,
    is_public BOOLEAN DEFAULT FALSE NOT NULL COMMENT '公開フラグ',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_travel_id (travel_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_storage_type (storage_type),
    
    FOREIGN KEY (travel_id) REFERENCES travels(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 4.2 マイグレーションファイル

```php
// database/migrations/2024_07_01_000001_create_photos_table.php
public function up()
{
    Schema::create('photos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('travel_id')->nullable()->constrained()->onDelete('set null');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->text('file_path');
        $table->text('thumbnail_path')->nullable();
        $table->string('original_filename');
        $table->unsignedInteger('file_size');
        $table->string('mime_type', 100);
        $table->unsignedInteger('width')->nullable();
        $table->unsignedInteger('height')->nullable();
        $table->text('caption')->nullable();
        $table->enum('storage_type', ['gcs'])->default('gcs');
        $table->boolean('is_public')->default(false);
        $table->timestamps();
        
        $table->index(['travel_id']);
        $table->index(['user_id']);
        $table->index(['created_at']);
        $table->index(['storage_type']);
    });
}
```

---

## 5. API設計

### 5.1 画像アップロード

#### エンドポイント
```
POST /api/photos
```

#### 認証
- 必須（Clerk認証）

#### リクエスト（multipart/form-data）
| パラメータ | 型 | 必須 | 説明 |
|-----------|----|------|------|
| image | file | ○ | 画像ファイル（最大5MB） |
| travel_id | int | × | 紐付ける旅行ID |
| caption | string | × | キャプション |
| is_public | boolean | × | 公開フラグ（デフォルト: false） |

#### バリデーション
```php
// app/Http/Requests/PhotoUploadRequest.php
public function rules()
{
    return [
        'image' => 'required|file|image|mimes:jpeg,png,jpg,webp|max:5120',
        'travel_id' => 'nullable|exists:travels,id',
        'caption' => 'nullable|string|max:1000',
        'is_public' => 'boolean',
    ];
}
```

#### レスポンス例
```json
{
  "success": true,
  "data": {
    "id": 101,
    "url": "https://storage.googleapis.com/traMemo-images/photos/2024/07/01/xxx.jpg",
    "thumbnail_url": "https://storage.googleapis.com/traMemo-images/thumbnails/2024/07/01/xxx_thumb.jpg",
    "travel_id": null,
    "caption": "集合写真",
    "file_size": 2048576,
    "width": 1920,
    "height": 1080,
    "storage_type": "gcs",
    "is_public": false,
    "created_at": "2024-07-01T10:00:00Z"
  }
}
```

### 5.2 画像取得

#### エンドポイント
```
GET /api/photos/{id}
```

#### 認証
- 不要（公開画像の場合）
- 必要（非公開画像の場合）

#### レスポンス例
```json
{
  "success": true,
  "data": {
    "id": 101,
    "url": "https://storage.googleapis.com/traMemo-images/photos/2024/07/01/xxx.jpg",
    "thumbnail_url": "https://storage.googleapis.com/traMemo-images/thumbnails/2024/07/01/xxx_thumb.jpg",
    "travel_id": 55,
    "caption": "集合写真",
    "file_size": 2048576,
    "width": 1920,
    "height": 1080,
    "storage_type": "gcs",
    "is_public": false,
    "created_at": "2024-07-01T10:00:00Z"
  }
}
```

### 5.3 画像削除

#### エンドポイント
```
DELETE /api/photos/{id}
```

#### 認証
- 必須（画像の所有者または管理者のみ）

#### レスポンス例
```json
{
  "success": true,
  "message": "Photo deleted successfully."
}
```

### 5.4 画像一覧取得（旅行記録単位）

#### エンドポイント
```
GET /api/travels/{travel_id}/photos
```

#### 認証
- 不要（公開旅行記録の場合）
- 必要（非公開の場合）

#### レスポンス例
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "url": "https://storage.googleapis.com/traMemo-images/photos/2024/07/01/xxx.jpg",
      "thumbnail_url": "https://storage.googleapis.com/traMemo-images/thumbnails/2024/07/01/xxx_thumb.jpg",
      "caption": "集合写真",
      "file_size": 2048576,
      "width": 1920,
      "height": 1080,
      "storage_type": "gcs",
      "is_public": false,
      "created_at": "2024-07-01T10:00:00Z"
    }
  ]
}
```

### 5.5 署名付きURL発行

#### エンドポイント
```
POST /api/photos/presign
```

#### 認証
- 必須

#### リクエスト
```json
{
  "filename": "photo.jpg",
  "content_type": "image/jpeg",
  "expires_in": 3600
}
```

#### レスポンス例
```json
{
  "success": true,
  "data": {
    "upload_url": "https://storage.googleapis.com/traMemo-images/photos/2024/07/01/xxx.jpg?GoogleAccessId=...&Expires=...&Signature=...",
    "file_path": "photos/2024/07/01/xxx.jpg",
    "expires_at": "2024-07-01T11:00:00Z"
  }
}
```

---

## 6. 実装例

### 6.1 PhotoController

```php
// app/Http/Controllers/Api/PhotoController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhotoUploadRequest;
use App\Application\Photo\UseCases\UploadPhotoUseCase;
use App\Application\Photo\UseCases\DeletePhotoUseCase;
use App\Application\Photo\UseCases\GetPhotoUseCase;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function __construct(
        private UploadPhotoUseCase $uploadPhotoUseCase,
        private DeletePhotoUseCase $deletePhotoUseCase,
        private GetPhotoUseCase $getPhotoUseCase
    ) {}

    public function upload(PhotoUploadRequest $request)
    {
        $result = $this->uploadPhotoUseCase->execute(
            $request->file('image'),
            $request->input('travel_id'),
            $request->input('caption'),
            $request->input('is_public', false)
        );

        return response()->json([
            'success' => true,
            'data' => $result
        ], 201);
    }

    public function show($id)
    {
        $photo = $this->getPhotoUseCase->execute($id);
        
        return response()->json([
            'success' => true,
            'data' => $photo
        ]);
    }

    public function destroy($id)
    {
        $this->deletePhotoUseCase->execute($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully.'
        ]);
    }

    public function presign(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'content_type' => 'required|string',
            'expires_in' => 'integer|min:300|max:3600'
        ]);

        $presignedUrl = $this->uploadPhotoUseCase->generatePresignedUrl(
            $request->input('filename'),
            $request->input('content_type'),
            $request->input('expires_in', 3600)
        );

        return response()->json([
            'success' => true,
            'data' => $presignedUrl
        ]);
    }
}
```

### 6.2 UploadPhotoUseCase

```php
// app/Application/Photo/UseCases/UploadPhotoUseCase.php
<?php

namespace App\Application\Photo\UseCases;

use App\Domain\Photo\Entities\Photo;
use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Infrastructure\Photo\Services\GCSImageService;
use Illuminate\Http\UploadedFile;

class UploadPhotoUseCase
{
    public function __construct(
        private PhotoRepositoryInterface $photoRepository,
        private GCSImageService $gcsImageService
    ) {}

    public function execute(
        UploadedFile $file,
        ?int $travelId,
        ?string $caption,
        bool $isPublic = false
    ): array {
        // 画像情報取得
        $imageInfo = getimagesize($file->getPathname());
        
        // GCSにアップロード
        $filePath = $this->gcsImageService->upload($file);
        $thumbnailPath = $this->gcsImageService->generateThumbnail($filePath);
        
        // DBに保存
        $photo = $this->photoRepository->create([
            'travel_id' => $travelId,
            'user_id' => auth()->id(),
            'file_path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
            'caption' => $caption,
            'storage_type' => 'gcs',
            'is_public' => $isPublic,
        ]);

        return [
            'id' => $photo->getId(),
            'url' => $this->gcsImageService->getPublicUrl($filePath),
            'thumbnail_url' => $this->gcsImageService->getPublicUrl($thumbnailPath),
            'travel_id' => $photo->getTravelId(),
            'caption' => $photo->getCaption(),
            'file_size' => $photo->getFileSize(),
            'width' => $photo->getWidth(),
            'height' => $photo->getHeight(),
            'storage_type' => $photo->getStorageType(),
            'is_public' => $photo->isPublic(),
            'created_at' => $photo->getCreatedAt()->toISOString(),
        ];
    }

    public function generatePresignedUrl(string $filename, string $contentType, int $expiresIn): array
    {
        $filePath = $this->gcsImageService->generateFilePath($filename);
        $presignedUrl = $this->gcsImageService->generatePresignedUrl($filePath, $contentType, $expiresIn);

        return [
            'upload_url' => $presignedUrl,
            'file_path' => $filePath,
            'expires_at' => now()->addSeconds($expiresIn)->toISOString(),
        ];
    }
}
```

### 6.3 GCSImageService

```php
// app/Infrastructure/Photo/Services/GCSImageService.php
<?php

namespace App\Infrastructure\Photo\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class GCSImageService
{
    private StorageClient $storage;
    private string $bucketName;

    public function __construct()
    {
        $this->storage = new StorageClient([
            'projectId' => config('filesystems.disks.gcs.project_id'),
            'keyFilePath' => config('filesystems.disks.gcs.key_file'),
        ]);
        $this->bucketName = config('filesystems.disks.gcs.bucket');
    }

    public function upload(UploadedFile $file): string
    {
        $filePath = $this->generateFilePath($file->getClientOriginalName());
        $bucket = $this->storage->bucket($this->bucketName);
        
        $bucket->upload(
            fopen($file->getPathname(), 'r'),
            [
                'name' => $filePath,
                'metadata' => [
                    'contentType' => $file->getMimeType(),
                ],
            ]
        );

        return $filePath;
    }

    public function generateThumbnail(string $originalPath): string
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($originalPath);
        
        // 画像をダウンロードしてサムネイル生成
        $imageContent = $object->downloadAsString();
        $image = Image::make($imageContent);
        $image->resize(300, 300, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        $thumbnailPath = str_replace('/photos/', '/thumbnails/', $originalPath);
        $thumbnailPath = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '_thumb.$1', $thumbnailPath);
        
        $bucket->upload(
            $image->encode()->getEncoded(),
            [
                'name' => $thumbnailPath,
                'metadata' => [
                    'contentType' => $image->mime(),
                ],
            ]
        );

        return $thumbnailPath;
    }

    public function getPublicUrl(string $filePath): string
    {
        return config('filesystems.disks.gcs.url') . '/' . $filePath;
    }

    public function generatePresignedUrl(string $filePath, string $contentType, int $expiresIn): string
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($filePath);

        return $object->signedUrl(new \DateTime("+{$expiresIn} seconds"), [
            'method' => 'PUT',
            'contentType' => $contentType,
        ]);
    }

    public function delete(string $filePath): void
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($filePath);
        $object->delete();
    }

    private function generateFilePath(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $newFilename = uniqid() . '_' . time() . '.' . $extension;
        
        return 'photos/' . date('Y/m/d') . '/' . $newFilename;
    }
}
```

---

## 7. フロントエンド（React）実装

### 7.1 画像アップロードコンポーネント

```typescript
// components/ImageUpload.tsx
import React, { useState, useCallback } from 'react';
import { useDropzone } from 'react-dropzone';

interface ImageUploadProps {
  onUpload: (files: File[]) => void;
  maxFiles?: number;
  maxSize?: number;
}

export const ImageUpload: React.FC<ImageUploadProps> = ({
  onUpload,
  maxFiles = 10,
  maxSize = 5 * 1024 * 1024, // 5MB
}) => {
  const [uploading, setUploading] = useState(false);

  const onDrop = useCallback(async (acceptedFiles: File[]) => {
    setUploading(true);
    try {
      onUpload(acceptedFiles);
    } finally {
      setUploading(false);
    }
  }, [onUpload]);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: {
      'image/*': ['.jpeg', '.jpg', '.png', '.webp']
    },
    maxFiles,
    maxSize,
  });

  return (
    <div
      {...getRootProps()}
      className={`border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors ${
        isDragActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'
      }`}
    >
      <input {...getInputProps()} />
      {uploading ? (
        <div className="flex items-center justify-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          <span className="ml-2">アップロード中...</span>
        </div>
      ) : (
        <div>
          <p className="text-lg font-medium">
            {isDragActive ? 'ここにドロップしてください' : 'クリックまたはドラッグ&ドロップ'}
          </p>
          <p className="text-sm text-gray-500 mt-2">
            JPEG, PNG, WebP形式、最大5MBまで
          </p>
        </div>
      )}
    </div>
  );
};
```

### 7.2 画像アップロードフック

```typescript
// hooks/useImageUpload.ts
import { useState } from 'react';
import { api } from '../lib/api';

interface UploadedImage {
  id: number;
  url: string;
  thumbnail_url: string;
  caption?: string;
  file_size: number;
  width?: number;
  height?: number;
}

export const useImageUpload = () => {
  const [uploading, setUploading] = useState(false);
  const [uploadedImages, setUploadedImages] = useState<UploadedImage[]>([]);

  const uploadImage = async (file: File, caption?: string): Promise<UploadedImage> => {
    const formData = new FormData();
    formData.append('image', file);
    if (caption) {
      formData.append('caption', caption);
    }

    const response = await api.post('/photos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    return response.data.data;
  };

  const uploadMultipleImages = async (files: File[], captions?: string[]) => {
    setUploading(true);
    try {
      const uploadPromises = files.map((file, index) =>
        uploadImage(file, captions?.[index])
      );
      
      const results = await Promise.all(uploadPromises);
      setUploadedImages(prev => [...prev, ...results]);
      
      return results;
    } finally {
      setUploading(false);
    }
  };

  const deleteImage = async (imageId: number) => {
    await api.delete(`/photos/${imageId}`);
    setUploadedImages(prev => prev.filter(img => img.id !== imageId));
  };

  return {
    uploading,
    uploadedImages,
    uploadImage,
    uploadMultipleImages,
    deleteImage,
  };
};
```

---

## 8. GCP設定手順

### 8.1 プロジェクト作成

1. [Google Cloud Console](https://console.cloud.google.com/)にアクセス
2. 新しいプロジェクトを作成（例: `traMemo-project`）
3. 請求を有効化

### 8.2 Cloud Storage設定

1. Cloud Storageを有効化
2. バケットを作成（例: `traMemo-images`）
3. バケットの設定：
   - リージョン: `asia-northeast1`（東京）
   - ストレージクラス: `Standard`
   - アクセス制御: `Fine-grained`

### 8.3 サービスアカウント作成

1. IAM & 管理 → サービスアカウント
2. 新しいサービスアカウントを作成
3. 役割を付与：
   - `Storage Object Admin`
   - `Storage Object Viewer`
4. JSONキーをダウンロード

### 8.4 Cloud CDN設定

1. Cloud CDNを有効化
2. 外部バックエンドを作成：
   - バケット: `traMemo-images`
   - プロトコル: `HTTPS`
3. URLマップを作成
4. キャッシュポリシーを設定

### 8.5 CORS設定

```json
[
  {
    "origin": ["https://your-frontend-domain.com"],
    "method": ["GET", "POST", "PUT", "DELETE"],
    "responseHeader": ["Content-Type", "Authorization"],
    "maxAgeSeconds": 3600
  }
]
```

---

## 9. セキュリティ設定

### 9.1 バケットアクセス制御

```json
{
  "bindings": [
    {
      "role": "roles/storage.objectViewer",
      "members": ["allUsers"]
    },
    {
      "role": "roles/storage.objectAdmin",
      "members": ["serviceAccount:your-service-account@your-project.iam.gserviceaccount.com"]
    }
  ]
}
```

### 9.2 署名付きURLの有効期限

- アップロード用: 1時間
- ダウンロード用: 24時間

### 9.3 ファイルサイズ制限

- 最大: 5MB
- 推奨: 2MB以下

---

## 10. 運用・監視

### 10.1 Cloud Monitoring設定

- ストレージ使用量の監視
- API呼び出し回数の監視
- エラー率の監視

### 10.2 ログ設定

```php
// config/logging.php
'channels' => [
    'gcs' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],
],
```

### 10.3 バックアップ戦略

- バケットのバージョニングを有効化
- 定期的なバックアップ（Cloud Storage Transfer Service）

---

## 11. コスト最適化

### 11.1 ストレージ最適化

- 画像の自動圧縮
- 不要なファイルの定期的削除
- ライフサイクルポリシーの設定

### 11.2 CDN最適化

- キャッシュ期間の最適化
- 画像サイズの最適化
- WebP形式の活用

---

## 12. 参考リンク

- [Google Cloud Storage公式ドキュメント](https://cloud.google.com/storage/docs)
- [Cloud CDN公式ドキュメント](https://cloud.google.com/cdn/docs)
- [Laravel Google Cloud Storage](https://github.com/spatie/laravel-google-cloud-storage)
- [Intervention Image](https://image.intervention.io/)

--- 