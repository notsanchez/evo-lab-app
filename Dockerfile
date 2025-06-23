# ───────────────────────── base ─────────────────────────
FROM php:8.3-fpm-alpine

# 1. libs + PHP extensions + tools
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --no-cache nginx supervisor git tzdata bash \
    && docker-php-ext-install pdo pdo_mysql mysqli sockets pcntl \
    && apk del .build-deps

# 2. timezone
ENV TZ=America/Sao_Paulo

# 3. Composer (prod)
RUN php -r "copy('https://getcomposer.org/installer', '/tmp/installer.php');" \
    && php /tmp/installer.php --install-dir=/usr/local/bin --filename=composer --quiet \
    && rm /tmp/installer.php

# 4. diretórios
WORKDIR /var/www/html
COPY public/ /var/www/html/
COPY websocket-server.php /var/www/html/
COPY nginx.conf   /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

# 5. permissões
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80 8080
CMD ["/usr/bin/supervisord","-c","/etc/supervisord.conf"]
