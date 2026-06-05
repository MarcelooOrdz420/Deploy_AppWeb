FROM unit:1.34.1-php8.3

WORKDIR /var/www/html

RUN if [ -f /etc/apt/sources.list.d/debian.sources ]; then sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources; fi \
    && if [ -f /etc/apt/sources.list ]; then sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list; fi \
    && apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .
COPY unit.json /docker-entrypoint.d/unit.json
COPY docker/00-app-setup.sh /docker-entrypoint.d/00-app-setup.sh

RUN chmod +x /docker-entrypoint.d/00-app-setup.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && chown -R unit:unit /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

CMD ["unitd", "--no-daemon"]
