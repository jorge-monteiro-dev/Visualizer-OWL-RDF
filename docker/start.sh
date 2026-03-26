#!/bin/sh
set -e

# Dossiers temporaires pour Nginx
mkdir -p /tmp/client_body /tmp/proxy /tmp/fastcgi /run/nginx

# Démarrer PHP-FPM en arrière-plan
php-fpm -D

# Vérifier que PHP-FPM écoute bien
sleep 1

# Tester la config Nginx avant de lancer
nginx -t

# Démarrer Nginx au premier plan
exec nginx -g "daemon off;"
