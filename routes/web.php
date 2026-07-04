<?php

use App\Core\Router;

$router = new Router();

$router->get('/', function () {
    echo 'Oshi-Wiki Framework OK';
});

return $router;