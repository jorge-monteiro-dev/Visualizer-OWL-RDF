FROM php:8.3-apache

# Désactiver les MPM en conflit, garder uniquement prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork rewrite

# Extensions PHP nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
        libxml2-dev unzip \
    && docker-php-ext-install opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier la config Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copier le projet
WORKDIR /var/www/html
COPY . .

# Installer Composer et les dépendances
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Créer le dossier uploads avec les bonnes permissions
RUN mkdir -p public/uploads && \
    chown -R www-data:www-data public/uploads && \
    chmod 755 public/uploads

# Config PHP optimisée
RUN echo "upload_max_filesize=15M\npost_max_size=16M\nmemory_limit=256M\nmax_execution_time=60\nopcache.enable=1" \
    > /usr/local/etc/php/conf.d/ontoviz.ini

EXPOSE 80