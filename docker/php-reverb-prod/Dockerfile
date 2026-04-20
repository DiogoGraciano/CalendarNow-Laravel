FROM serversideup/php:8.5-cli-alpine

ENV PHP_OPCACHE_ENABLE=1 \
    APP_BASE_DIR="/var/www/html/public" \
    SSL_MODE="mixed" \
    PHP_MEMORY_LIMIT="1024M"

ARG user=www-data

USER root

RUN apk update && apk add --no-cache \
    git curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip unzip libzip-dev \
    postgresql-dev \
    vips-dev \
    libffi-dev \
    ffmpeg \
    netcat-openbsd \
    autoconf g++ make \
    linux-headers \
    nodejs npm \
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
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        sockets \
        soap \
        ffi && \
    pecl install vips excimer && \
    docker-php-ext-enable vips excimer ffi && \
    echo "ffi.enable=true" >> /usr/local/etc/php/conf.d/docker-php-ext-ffi.ini && \
    echo "zend.max_allowed_stack_size = -1" >> /usr/local/etc/php/conf.d/stack.ini && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apk del $PHPIZE_DEPS && \
    rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

COPY --chown=www-data:www-data ./src/backend /var/www/html

WORKDIR /var/www/html

USER www-data

RUN composer install --no-interaction --prefer-dist

CMD ["php", "artisan", "reverb:start", "--host=0.0.0.0", "--port=8080"]