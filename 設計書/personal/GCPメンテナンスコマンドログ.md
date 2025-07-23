# ローカルからのGCP SQL接続
2. 認証の設定
## サービスアカウントの作成
gcloud iam service-accounts create cloud-sql-proxy --display-name="Cloud SQL Proxy Service Account"

## 必要な権限を付与
gcloud projects add-iam-policy-binding robotic-sky-465306-n5 --member="serviceAccount:cloud-sql-proxy@robotic-sky-465306-n5.iam.gserviceaccount.com" --role="roles/cloudsql.client"

## キーファイルの作成
gcloud iam service-accounts keys create key.json --iam-account=cloud-sql-proxy@robotic-sky-465306-n5.iam.gserviceaccount.com

Auth Proxyの起動：
## サービスアカウントキーを使用する場合
./cloud-sql-proxy.exe --credentials-file=./key/robotic-sky-465306-n5-716b37bf4f8e.json robotic-sky-465306-n5:asia-northeast1:tramemo-db --port=3307


# configキャッシュクリア用ジョブ作成コマンド
gcloud run jobs create config-clear --image gcr.io/robotic-sky-465306-n5/tramemo-api:latest --command="php" --args="artisan,config:clear" --region=asia-northeast1

# アクセス検証用
https://tramemo-api-681273518185.asia-northeast1.run.app/api/travels/1

# シーダー実行用ジョブ作成コマンド
gcloud run jobs create db-seed --image gcr.io/robotic-sky-465306-n5/tramemo-api:latest --command="php" --args="artisan,db:seed,--force" --region=asia-northeast1 --set-cloudsql-instances=robotic-sky-465306-n5:asia-northeast1:tramemo-db --set-env-vars="DB_DATABASE=tramemo_production,DB_USERNAME=tramemo_user,DB_SOCKET=/cloudsql/robotic-sky-465306-n5:asia-northeast1:tramemo-db" --set-secrets="DB_PASSWORD=db-password:latest"

# APIサーバ用のDockerfileを修正して再度実行
### ステップ6: Dockerイメージビルド・プッシュ
#### 6.2 イメージビルド・プッシュ
```bash
# プロジェクトルートで実行
docker build -t gcr.io/robotic-sky-465306-n5/tramemo-api:v1.2.0 -f infra/gcp/docker/Dockerfile .
docker push gcr.io/robotic-sky-465306-n5/tramemo-api:v1.2.0
```
#### ローカルでのDockerビルド検証用
docker build -f infra/gcp/docker/Dockerfile -t tramemo-api-local:latest .
docker run --rm --name tramemo-api-test -p 8080:8080 --env-file ./src/.env tramemo-api-local:latest