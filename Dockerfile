FROM php:8.3-fpm-alpine

# Extensions PHP
RUN apk add --no-cache nginx libxml2-dev && \
    docker-php-ext-install opcache

# Config PHP
RUN echo "upload_max_filesize=15M" >  /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "post_max_size=16M"       >> /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "memory_limit=256M"       >> /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "max_execution_time=60"   >> /usr/local/etc/php/conf.d/ontoviz.ini

# Config Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Projet
WORKDIR /var/www/html
COPY . .

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN mkdir -p public/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod 755 public/uploads

# Script de démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
