<?php

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;


require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/users', function ($request, $response) {	
    $users = json_decode($request->getCookieParam('users', json_encode([])), true);
    $params = [
        'users' => $users
    ];

    return $this->get('renderer')->render($response, "users/index.phtml", $params);
});

$app->get('/users/new', function ($request, $response) {
    	
    $params = [
        'user' => [],
        //'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->post('/users', function ($request, $response) {
    $user = $request->getParsedBodyParam('user');
    $users = json_decode($request->getCookieParam('users', json_encode([])));
    $users[] = $user;
    $encodedUsers = json_encode($users);

    return $response->withHeader('Set-Cookie', "users={$encodedUsers};Path=/users")
        ->withRedirect('/users');
});


$app->run();

