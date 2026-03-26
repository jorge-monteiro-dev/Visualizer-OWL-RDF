FROM php:8.3-apache

# Extensions PHP nécessaires
RUN docker-php-ext-install opcache && \
    apt-get update && apt-get install -y --no-install-recommends \
        libxml2-dev unzip curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite pour Apache
RUN a2enmod rewrite

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
RUN echo "upload_max_filesize=15M\n\
post_max_size=16M\n\
memory_limit=256M\n\
max_execution_time=60\n\
opcache.enable=1\n\
opcache.memory_consumption=64" > /usr/local/etc/php/conf.d/ontoviz.ini

EXPOSE 80
