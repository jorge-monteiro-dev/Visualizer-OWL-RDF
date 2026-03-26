<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');

require ROOT_PATH . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Request;

// Ensure upload directory exists
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

$router = new Router();
$request = new Request();

require ROOT_PATH . '/routes/web.php';

$router->dispatch($request);
