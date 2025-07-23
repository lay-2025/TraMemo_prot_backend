# GCP Cloud Run＋Nginx＋DockerでSPA（Viteビルド）を公開する手順書

---

## 目次
1. はじめに（概要・前提）
2. 事前準備
3. Vite本番ビルド
4. Dockerイメージ作成
5. GCRへのイメージpush
6. Cloud Runへのデプロイ
7. 動作確認
8. 補足（.env.production, CORS, よくある質問）
9. 参考リンク

---

## 1. はじめに（概要・前提）
ViteでビルドしたSPA（Single Page Application）を、Nginxで配信するDockerイメージとしてGoogle Cloud Runにデプロイし、インターネット公開する手順をまとめます。

- Dockerfileの格納場所例：`infra/gcp_front/docker/Dockerfile`
- APIサーバはCloud Run上の別サービスとして動作し、`VITE_API_BASE_URL`で連携します。
- 想定読者：GCP・Docker・Viteの基本操作が分かる方

---

## 2. 事前準備
- GCPプロジェクト作成
- gcloud CLIインストール
- GCP認証・プロジェクト設定

```sh
gcloud auth login
gcloud config set project [YOUR_PROJECT_ID]
```

---

## 3. Vite本番ビルド

```sh
npm run build
```
- `dist/`フォルダが生成されます。

---

## 4. Dockerイメージ作成

### 4-1. Dockerfileの配置と内容
`infra/gcp_front/docker/`ディレクトリに以下の内容で`Dockerfile`を作成します。

```Dockerfile
# 1. ビルド用ステージ
FROM node:20 AS build
WORKDIR /app
COPY ../../../package.json ../../../package-lock.json ./
RUN npm ci
COPY ../../../ ./
RUN npm run build
# 2. 配信用ステージ
FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY infra/gcp_front/docker/nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
```

### 4-2. nginx.conf（SPA対応）
同じディレクトリに`nginx.conf`を作成：

```nginx
server {
  listen 80;
  server_name _;
  root /usr/share/nginx/html;
  index index.html;
  location / {
    try_files $uri $uri/ /index.html;
  }
}
```

### 4-3. .env.productionの配置
- プロジェクトルートに`.env.production`を作成し、本番用APIエンドポイント等を記載します。
- 例：
  ```env
  VITE_API_BASE_URL=https://api-xxxxxx-uc.a.run.app
  VITE_USE_MOCK_MAP=false
  ```
- `.env.production`は`docker build`時に自動で読み込まれます。
- `.dockerignore`で除外しないよう注意。
- 必要に応じて`COPY ../../../.env.production .`で明示的にコピー。

---

## 5. GCRへのイメージpush

```sh
# Dockerビルド
# プロジェクトルートで実行
docker build -t gcr.io/[YOUR_PROJECT_ID]/tramemo-front:latest -f infra/gcp_front/docker/Dockerfile .
# GCRへpush
docker push gcr.io/[YOUR_PROJECT_ID]/tramemo-front:latest
```

---

## 6. Cloud Runへのデプロイ

```sh
gcloud run deploy tramemo-front \
  --image gcr.io/[YOUR_PROJECT_ID]/tramemo-front:latest \
  --platform managed \
  --region asia-northeast1 \
  --allow-unauthenticated
```
- `--allow-unauthenticated`で全世界公開
- `--region`は任意

---

## 7. 動作確認

### 7-1. ローカルでのDocker動作確認
```sh
docker run --rm -p 8080:80 tramemo-front-local
```
- [http://localhost:8080](http://localhost:8080) にアクセスし、SPAが正しく表示されるか確認
- API連携が必要な場合は、`.env`や`VITE_API_BASE_URL`の値がローカル用になっているかも確認

### 7-2. Cloud Run公開後の確認
- Cloud RunのサービスURLにアクセスし、画面表示・API連携を確認

---

## 8. 補足

### 8-1. .env.production・環境変数の扱い
- Viteは`VITE_`で始まる環境変数のみビルド時に埋め込む
- `.env.production`はビルド時にのみ参照される（Cloud Runの環境変数上書きは不可）
- 本番用の値は必ずビルド時点で`.env.production`に記載

#### 【詳細】
##### .env.productionファイルの作成と配置
- 本番用のAPIエンドポイントなどを指定するため、プロジェクトルートに`.env.production`ファイルを作成します。
- 例：
  ```env
  VITE_API_BASE_URL=https://api-xxxxxx-uc.a.run.app
  VITE_USE_MOCK_MAP=false
  ```
- `.env.production`は`docker build`時に自動で読み込まれ、`vite build`コマンド実行時に反映されます。
- `.env`（開発用）と`.env.production`（本番用）は用途に応じて使い分けてください。

##### Dockerビルド時の注意
- Dockerfileで`npm run build`を実行する際、ビルドコンテキストに`.env.production`が含まれている必要があります。
- `.dockerignore`で`.env.production`を除外しないよう注意してください。
- 必要に応じて、`COPY ../../../.env.production .` などで明示的にコピーしてください。

##### Cloud Runでの環境変数上書き
- Cloud Runの管理画面や`gcloud run deploy`コマンドの`--set-env-vars`オプションで環境変数を上書きすることも可能です。
- ただし、Viteはビルド時にしか環境変数を参照しないため、**ビルド後にCloud Runで上書きしても反映されません**。
- 本番用の値は必ずビルド時点で`.env.production`に記載してください。

### 8-2. APIサーバとの連携
- Cloud RunでAPIサーバも別サービスとしてデプロイし、外部公開URL（例: `https://api-xxxxxx-uc.a.run.app`）を取得
- フロントエンドの`.env.production`で`VITE_API_BASE_URL`を指定

### 8-3. CORSの注意点
- APIサーバ側でCORS（クロスオリジン）設定が必要
  - 例：`Access-Control-Allow-Origin: *` もしくはフロントエンドのURLを指定
- Cloud Run同士の通信はインターネット経由（HTTPS）

### 8-4. よくある質問・トラブルシュート
- ポートの指定（Cloud Runは8080番を推奨）
- ファイルパスやディレクトリ構成の注意
- Dockerビルド時のエラー例と対処

---

## 9. 参考リンク
- [Cloud Run 公式ドキュメント](https://cloud.google.com/run/docs)
- [Google Container Registry 公式ドキュメント](https://cloud.google.com/container-registry/docs)
- [Nginx公式：SPA対応設定](https://nginx.org/en/docs/http/ngx_http_core_module.html#try_files)

---

何か問題が発生した場合や、独自ドメイン・HTTPS強制・Cloud CDN連携などの追加要件がある場合は、別途ご相談ください。 