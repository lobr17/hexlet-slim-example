<?php

use Slim\Factory\AppFactory;
use DI\Container;
use function Symfony\Component\String\s;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$repo = new App\UserRepository();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) use ($router) {
    $router->urlFor('users');

    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/users', function ($request, $response) use ($repo) {
    $flash = $this->get('flash')->getMessages();		
    $users = $repo->all();
    $params = [
        'users' => $users,
        'flash' => $flash
    ];
    return $this->get('renderer')->render($response, "users/index.phtml", $params);
})->setName('users');

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['title' => '']
    ];

    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->post('/users', function ($request, $response) use ($repo) {
    $user = $request->getParsedBodyParam('user');    
    $repo->save($user);

    $this->get('flash')->addMessage('success', 'User Added');

    return $response->withRedirect('/users');
});

$app->run();

