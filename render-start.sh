#!/usr/bin/env bash
set -e

# SQLiteファイルの準備
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chown -R www-data:www-data /var/www/html/database

# 画像表示用のシンボリックリンク作成
php artisan storage:link --force

# マイグレーションの自動実行
php artisan migrate --force

# 画像アップロードのフィーチャーフラグをONにする
php artisan feature:toggle image.upload --on

# キャッシュのクリアと本番最適化
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Apacheサーバーの起動
apache2-foreground