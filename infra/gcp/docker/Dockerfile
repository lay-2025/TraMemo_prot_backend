# docker buildはプロジェクトルートで実行
# dockerignoreもプロジェクトルートに配置
FROM php:8.2-fpm-alpine

# 必要なパッケージをインストール
RUN apk add --no-cache \
    nginx \
    wget \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    freetype-dev \
    jpeg-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# nginxの設定ファイル配置
RUN mkdir -p /run/nginx
COPY infra/gcp/docker/nginx.conf /etc/nginx/nginx.conf

# アプリケーション配置
RUN mkdir -p /app
COPY src /app

# Composerインストール
RUN wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer
RUN cd /app && /usr/local/bin/composer install --no-dev --optimize-autoloader

# 権限調整
RUN chown -R www-data: /app

# スタートアップスクリプト配置
COPY infra/gcp/docker/startup.sh /app/docker/startup.sh
RUN chmod +x /app/docker/startup.sh

# 起動コマンド
CMD sh /app/docker/startup.sh