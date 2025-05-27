FROM php:8.3-fpm-alpine3.20
RUN set -ex;
RUN apk update && \
    apk upgrade

WORKDIR /var/www/php


# permanent
RUN apk add vim; \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer;

# needed for build, will be cleaned-up at the end
RUN apk add --no-cache --virtual .build-deps \
            git \
            openssl-dev \
            libzip-dev \
            libjpeg-turbo-dev \
            libpng-dev \
            icu-data-full \
            icu-dev \
            jq \
            linux-headers \
            ${PHPIZE_DEPS};
# mysql and netcat for database connection check
RUN apk add --no-cache \
    mariadb-dev \
    netcat-openbsd \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql
# debug
RUN pecl install xdebug pcov;

# other exts
RUN docker-php-ext-configure pcntl --enable-pcntl;
RUN docker-php-ext-install zip bcmath intl pcntl;

# cleanup
RUN runDeps="$( \
      scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
        | tr ',' '\n' \
        | sort -u \
        | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --no-cache --virtual .image-dependencies git openssh-client $runDeps; \
    \
    apk del .build-deps; \
    \
    rm -rf /tmp/*

# Copy entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
