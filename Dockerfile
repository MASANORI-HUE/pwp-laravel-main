FROM php:8.4-apache
# 必要なOSパッケージとPHP拡張モジュール（SQLite・画像処理GDなど）をインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite zip gd bcmath

# Apacheのmod_rewriteを有効化し、公開ディレクトリを /public に変更
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Composerの導入
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# プロジェクトのファイルをコピー
COPY . .

# PHPパッケージのインストール（本番用）とフロントエンド（Tailwind CSS等）のビルド
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# ストレージとキャッシュの書き込み権限を設定
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# 起動スクリプトに実行権限を付与して実行
RUN chmod +x /var/www/html/render-start.sh
CMD ["/var/www/html/render-start.sh"]