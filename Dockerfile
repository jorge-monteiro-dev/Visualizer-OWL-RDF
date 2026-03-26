FROM php:8.3-fpm-alpine

# Nginx + libxml2
RUN apk add --no-cache nginx libxml2-dev

# Extensions PHP
RUN docker-php-ext-install opcache

# Config PHP
RUN echo "upload_max_filesize=15M" >  /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "post_max_size=16M"       >> /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "memory_limit=256M"       >> /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "max_execution_time=60"   >> /usr/local/etc/php/conf.d/ontoviz.ini

# Projet
WORKDIR /var/www/html
COPY . .

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Config Nginx — remplace la config globale
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Script démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Permissions uploads
RUN mkdir -p public/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod 755 public/uploads

EXPOSE 80
CMD ["/start.sh"]
