#!/bin/sh
set -e

# Créer les dossiers temporaires Nginx
mkdir -p /tmp/client_body /tmp/proxy /tmp/fastcgi

# Démarrer PHP-FPM en arrière-plan
php-fpm -D

# Démarrer Nginx au premier plan (garde le conteneur vivant)
exec nginx -g "daemon off;"
