FROM php:8.1-cli-buster

# Arguments defined in docker-compose.yml
ARG user=dev
ARG uid=1000

# Установить системные зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    curl \
    git \
    zip \
    unzip \
    procps

# Очистить кэш
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Получить последнюю версию Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Установить расширения PHP
RUN docker-php-ext-install \
    pdo_mysql \
    sockets \
    pdo_pgsql \
    pcntl

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
