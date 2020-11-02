<?php


require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    
    
    // Благодаря пакету slim/http этот же код можно записать короче
     return $response->write('Welcome to Slim!');
});
$app->run();
