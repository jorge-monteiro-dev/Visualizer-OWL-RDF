FROM php:8.3-fpm-alpine

# Nginx + libxml2 + dépendances pour SimpleXML
RUN apk add --no-cache nginx libxml2-dev

# Extensions PHP (simplexml est utilisé par OntologyParser)
RUN docker-php-ext-install opcache simplexml

# Config PHP
RUN echo "upload_max_filesize=15M" >  /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "post_max_size=16M"       >> /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "memory_limit=256M"       >> /usr/local/etc/php/conf.d/ontoviz.ini && \
    echo "max_execution_time=60"   >> /usr/local/etc/php/conf.d/ontoviz.ini

# Projet
WORKDIR /var/www/html
COPY . .

# Supprimer le vendor commité s'il existe, puis réinstaller proprement
RUN rm -rf vendor

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Config Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Script démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Permissions uploads
RUN mkdir -p public/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod 755 public/uploads

EXPOSE 8080
CMD ["/start.sh"]
