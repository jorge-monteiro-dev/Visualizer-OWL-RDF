FROM php:8.3-apache

# Désactiver mpm_event (chargé par défaut), activer mpm_prefork + rewrite
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
          /etc/apache2/mods-enabled/mpm_worker.load 2>/dev/null; \
    ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load && \
    ln -sf /etc/apache2/mods-available/rewrite.load     /etc/apache2/mods-enabled/rewrite.load

# Extensions PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
        libxml2-dev unzip \
    && docker-php-ext-install opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Config Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Projet
WORKDIR /var/www/html
COPY . .

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Uploads
RUN mkdir -p public/uploads && \
    chown -R www-data:www-data public/uploads && \
    chmod 755 public/uploads

# Config PHP
RUN echo "upload_max_filesize=15M\npost_max_size=16M\nmemory_limit=256M\nmax_execution_time=60\nopcache.enable=1" \
    > /usr/local/etc/php/conf.d/ontoviz.ini

EXPOSE 80