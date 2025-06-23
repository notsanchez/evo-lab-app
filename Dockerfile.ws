# Dockerfile.ws
FROM php:8.3-cli-alpine

# 1) add build-deps para compilar extensões
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --no-cache git tzdata \
    && docker-php-ext-install sockets pcntl \
    && apk del .build-deps                     # limpa

# 2) Composer
RUN php -r "copy('https://getcomposer.org/installer','/tmp/installer.php');" \
    && php /tmp/installer.php --install-dir=/usr/local/bin --filename=composer --quiet \
    && rm /tmp/installer.php

# 3) código & dependências
WORKDIR /app
COPY composer.json composer.lock /app/
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts

COPY websocket-server.php /app/

EXPOSE 8080
CMD ["php","/app/websocket-server.php"]
