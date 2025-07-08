# TraMemo プロジェクト DDD構成仕様

## 概要

本プロジェクトは、旅行記録SNS「TraMemo」のバックエンドAPIをLaravelで実装しています。  
ドメイン駆動設計（DDD）を採用し、保守性・拡張性・責務分離を重視した構成です。

---

## ディレクトリ構成

```
app/
├── Domain/                             # ビジネスロジックの中心。各ドメインごとにEntities（エンティティ・値オブジェクト等）、Repositories（リポジトリインターフェース）を配置
│   ├── Travel/
│   │   ├── Entities/                   # Travelドメインのエンティティ（集約ルートや値オブジェクト等）
│   │   └── Repositories/               # Travelドメインのリポジトリインターフェース
│   ├── Photo/
│   │   ├── Entities/
│   │   └── Repositories/
│   └── User/
│       ├── Entities/
│       └── Repositories/
├── Application/                        # アプリケーション層。ユースケースやアプリケーションサービスを配置。複数ドメインのリポジトリを組み合わせて業務処理を実現
│   └── User/
│   │   └── UseCases/                   # User関連のユースケース
│   │   └── Services/                   # User関連のアプリケーションサービス（例：認証済みユーザー取得など横断的な処理）
│   └── ...                             # 他ドメインも同様
├── Infrastructure/                     # インフラ層。DBや外部サービスとの連携。Eloquentモデルやリポジトリ実装を配置
│   ├── Travel/
│   │   ├── Models/                     # Eloquentモデル
│   │   └── Repositories/               # リポジトリ実装
│   ├── Photo/
│   │   ├── Models/
│   │   └── Repositories/
│   └── User/
│       ├── Models/
│       └── Repositories/
├── Http/
│   ├── Controllers/                    # コントローラ。APIエンドポイントの受付。リクエストのバリデーション、ユースケース呼び出し、レスポンス返却を担当
│   │   ├── Api/                        # API用コントローラ
│   │   ├── Middleware/                 # ミドルウェア。認証・認可・リクエスト加工などを配置
│   │   └── Resources/                  # リソース。APIレスポンスを整形するResourceクラスを配置
│   └── Resources/                      # レスポンス返却に関するファイル（Resourceクラス等）を配置
```

---

## レイヤーの役割

- **Domain層**  
  ビジネスロジックの中心。エンティティ、値オブジェクト、リポジトリインターフェースなどを配置。

- **Application層**  
  ユースケース（アプリケーションサービス）を配置。複数ドメインのリポジトリを組み合わせて業務処理を実現。
  各ドメイン配下に `UseCases/` ディレクトリを設け、主要な業務処理をユースケースとして実装します。  
  また、`Services/` ディレクトリには「認証済みユーザー取得」など、ユースケースから共通的に利用されるアプリケーションサービスやヘルパーを配置します。  

- **Infrastructure層**  
  データベースや外部サービスとの連携。Eloquentモデルやリポジトリ実装を配置。

- **Http/Controllers層**  
  APIエンドポイントの定義。リクエスト受付・バリデーション・ユースケース呼び出し・レスポンス返却を担当。

---

---

## ドメイン一覧

- **Travel**　　旅行記録本体、旅行スポット（TravelSpot）、旅行の公開範囲や都道府県など
- **Photo**　　 旅行に紐づく写真の管理
- **Tag**　　　タグの管理と旅行との紐付け（多対多）
- **Comment**　コメント管理
- **Favorite**　お気に入り管理
- **Like**　　　いいね管理
- **User**　　　ユーザー管理・認証・プロフィール

---

## 主要な処理の流れ（例：旅行記録作成）

1. **コントローラ**  
   リクエストを受け取り、バリデーション後、ユースケースを呼び出す。

2. **ユースケース（UseCase）**  
   Travel/Photo/Tag各リポジトリを呼び出し、トランザクション内で一括処理。

3. **リポジトリ**  
   各ドメインごとに永続化処理を実装（例：TravelRepository, PhotoRepository, TagRepository）。

4. **モデル**  
   EloquentモデルはInfrastructure層のModels配下に配置。

---

## 命名規則・設計方針

- ドメインごとにディレクトリを分割
- リポジトリはインターフェース（Domain）と実装（Infrastructure）で分離
- ユースケースはApplication層に配置し、複数リポジトリを組み合わせる
- モデル名・テーブル名・APIパスは「travel」「photo」「tag」など英語小文字複数形を基本とする

---

## 参考：旅行記録作成APIの流れ

1. `/api/travels` へPOSTリクエスト
2. TravelControllerがバリデーション後、CreateTravelUseCaseを呼び出し
3. UseCaseがTravelRepository, PhotoRepository, TagRepositoryを順に呼び出し
4. travels, travel_spots, photos, tags, travel_tag（pivot）テーブルへデータ保存

---

## 今後の拡張

- ドメイン追加時はDomain/Application/Infrastructure配下に同様の構成で追加
- ユースケースが複雑化した場合はサービスクラスやDTOの導入も検討

---

## 補足

- コード例や詳細な設計方針は `/docs/` 配下やNotion仕様書も参照してください
- 質問や不明点はSlackまたはGitHub Discussionsで随時受け付けています