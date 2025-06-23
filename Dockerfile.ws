# Dockerfile.ws
FROM php:8.3-cli-alpine

# sockets e pcntl são exigidos pelo Ratchet
RUN apk add --no-cache git tzdata \
 && docker-php-ext-install sockets pcntl

# Composer
RUN php -r "copy('https://getcomposer.org/installer','/tmp/installer.php');" \
 && php /tmp/installer.php --install-dir=/usr/local/bin --filename=composer --quiet \
 && rm /tmp/installer.php

WORKDIR /app
COPY composer.json composer.lock /app/
RUN composer install --no-dev --prefer-dist --no-interaction

COPY websocket-server.php /app/

EXPOSE 8080
CMD ["php","/app/websocket-server.php"]
