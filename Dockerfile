# FROM laravel/sail:latest
FROM laravelsail/php82-composer:latest

# cronのインストール
RUN apt-get update && apt-get install -y cron