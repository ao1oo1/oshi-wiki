<?php

use App\Core\Router;
use App\Controllers\Public\HomeController;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

return $router;