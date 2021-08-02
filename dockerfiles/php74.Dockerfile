FROM php:7.4.16-cli-buster

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

# Установить расширения PHP
RUN docker-php-ext-install \
    pdo_mysql \
    sockets \
    pdo_pgsql \
    pcntl
