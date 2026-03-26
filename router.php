<?php
/**
 * Router script pour le serveur de développement PHP intégré.
 * Usage : php -S localhost:8080 -t public router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

// Servir les fichiers statiques directement
$staticFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($staticFile) && !is_dir($staticFile)) {
    return false; // Le serveur PHP sert le fichier directement
}

// Tout le reste → index.php
require __DIR__ . '/public/index.php';
