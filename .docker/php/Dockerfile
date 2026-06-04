# .docker/php/Dockerfile
FROM php:8.3-fpm

# 1. Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libicu-dev \
    gnupg2 \
    ca-certificates \
    supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Установка Node.js 20 и npm через apt
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Установка расширений PHP
RUN docker-php-ext-install pdo_pgsql pgsql gd zip intl

# 4. Установка Redis через git clone (альтернатива pecl)
RUN git clone https://github.com/phpredis/phpredis.git /tmp/phpredis \
    && cd /tmp/phpredis \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/phpredis

# 5. Копируем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Настройка рабочей директории
WORKDIR /var/www

# 7. Конфигурация PHP-FPM для прослушивания на всех интерфейсах
RUN echo "listen = 0.0.0.0:9000" >> /usr/local/etc/php-fpm.d/www.conf

# 8. Конфигурация supervisord
COPY .docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 8. Проброс портов
EXPOSE 9000 5173

# 9. Запуск supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]