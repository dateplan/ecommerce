FROM php:8.1-fpm

# 必要なパッケージをインストール
RUN apt-get update && \
    apt-get install -y \
      unzip \
      libpng-dev \
      libjpeg-dev \
      libfreetype6-dev \
      libzip-dev \
      libonig-dev \
      libicu-dev \
      libsodium-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# GDとその他の拡張をインストール
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql intl sodium exif calendar

# Composerをインストール
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# php.iniをコピー
COPY php.ini /usr/local/etc/php/conf.d/

WORKDIR /var/www/html
