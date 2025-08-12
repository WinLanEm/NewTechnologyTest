FROM php:8.2-fpm

# 1. Установка зависимостей
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

# 2. Копирование конфигов PHP
COPY ./_docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

# 3. Composer для php (можно оставить root, чтобы не ловить ошибки)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# 4. Рабочая директория
WORKDIR /var/www

# 5. Установка зависимостей через Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 6. Копирование исходников
COPY . .

# 7. Настройка прав для директорий, нужных Supervisor и cron, чтобы www-data всё мог писать
RUN mkdir -p /var/run/supervisor /var/log/supervisor /var/www/storage/logs \
    && chown -R www-data:www-data /var/run/supervisor /var/log/supervisor /var/www/storage/logs \
    && chmod -R 775 /var/run/supervisor /var/log/supervisor /var/www/storage/logs

# 8. Копирование конфига Supervisor
COPY _docker/app/supervisord.conf /etc/supervisor/supervisord.conf

# 9. Переключение на пользователя www-data
USER www-data

# 10. Запуск Supervisor (единственный командный процесс)
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
