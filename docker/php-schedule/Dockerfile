FROM serversideup/php:8.5-cli-alpine

ENV PHP_OPCACHE_ENABLE=1 \
    APP_BASE_DIR="/var/www/html/public" \
    PHP_MEMORY_LIMIT="256M"

ARG user=www-data

USER root

RUN apk update && apk add --no-cache \
    git curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip unzip libzip-dev \
    postgresql-dev \
    libffi-dev \
    netcat-openbsd \
    autoconf g++ make \
    linux-headers \
    freetype-dev \
    jpeg-dev \
    libjpeg-turbo-dev \
    $PHPIZE_DEPS && \
    docker-php-ext-configure pgsql && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        sockets && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apk del $PHPIZE_DEPS && \
    rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

WORKDIR /var/www/html

USER www-data

CMD ["php", "artisan", "schedule:work"]
