FROM phpswoole/swoole:php8.5-alpine

ARG user=www-data

RUN apk add --no-cache \
    git curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip unzip libzip-dev \
    postgresql-dev \
    vips-dev \
    libffi-dev \
    libgcc \
    ffmpeg \
    netcat-openbsd \
    autoconf g++ make \
    linux-headers \
    freetype-dev \
    jpeg-dev \
    libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
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
        ffi \
    && pecl install vips excimer redis \
    && docker-php-ext-enable vips excimer redis \
    && echo "ffi.enable=true" >> /usr/local/etc/php/conf.d/docker-php-ext-ffi.ini \
    && echo "zend.max_allowed_stack_size = -1" >> /usr/local/etc/php/conf.d/stack.ini

RUN echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_input_vars = 5000" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

COPY ./docker/php/entrypoint.sh /etc/entrypoint.sh

RUN chmod +x /etc/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80

ENTRYPOINT ["/etc/entrypoint.sh"]