FROM php:8.2-fpm-alpine

# Установка системных зависимостей
RUN apk add --no-cache \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    icu-dev \
    nginx \
    supervisor \
    postgresql-client

# Установка PHP расширений
RUN docker-php-ext-install \
    pdo_pgsql \
    zip \
    intl \
    opcache

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Настройка Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Рабочая директория
WORKDIR /var/www/html

# Копирование исходного кода
COPY . .

# Установка зависимостей (без скриптов, так как БД может быть еще не готова)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts


# Права доступа
RUN mkdir -p var vendor && chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Копирование скрипта входа
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
