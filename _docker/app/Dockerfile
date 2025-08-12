FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    cron \
    supervisor \
    procps \
    apt-utils \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libmongoc-dev \
    libbson-dev \
    libssl-dev \
    zip unzip \
    git \
    && docker-php-ext-install pdo_mysql pdo_pgsql bcmath gd zip sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ./_docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin


WORKDIR /var/www


COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts


COPY . .

RUN mkdir -p /var/run/supervisor /var/log/supervisor /var/www/storage/logs \
    && chown -R www-data:www-data /var/run/supervisor /var/log/supervisor /var/www/storage/logs \
    && chmod -R 775 /var/run/supervisor /var/log/supervisor /var/www/storage/logs

COPY _docker/app/supervisord.conf /etc/supervisor/supervisord.conf

USER www-data

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
