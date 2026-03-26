<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\OntologyController;

$router->get('/', [HomeController::class, 'index']);
$router->post('/upload', [OntologyController::class, 'upload']);
$router->get('/visualize', [OntologyController::class, 'visualize']);
$router->get('/api/graph', [OntologyController::class, 'graphData']);
$router->get('/api/demo', [OntologyController::class, 'demoData']);
$router->post('/api/parse', [OntologyController::class, 'parseInline']);
