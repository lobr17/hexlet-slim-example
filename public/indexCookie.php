<?php

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use App\Validator;

require __DIR__ . '/../vendor/autoload.php';

session_start();
//setcookie();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);

//$repo = new App\UserRepository();

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) {
    
    $allUser = json_decode($request->getCookieParam('allUser', json_encode([])), true);
    $params = [
        'allUser' => $allUser
    ];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
});

/*
$app->get('/users', function ($request, $response) use ($router) {
    $flash = $this->get('flash')->getMessages();		
    
    $user = $request->getParsedBodyParam('user');
    $allUsers = json_decode($request->getCookieParam('user', json_encode([])));

    $allUsers[] = $user; 

    
    return $this->get('renderer')->render($response, "users/index.phtml", $params);
})->setName('users');

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => [],
        'errors' => []
    ];

    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});
 */

$app->post('/users', function ($request, $response) use ($router) {
    // Извлекаем данные формы
    $user = $request->getParsedBodyParam('user');
    $allUser = json_decode($request->getCookieParam('allUser', json_encode([])), true);

    $id = $user['id'];
    $allUser[$id] = ['name' => $user['name']];

    // Кодирование 
    $encodedAllUser = json_encode($allUser);

    // Установка в куку
    return $response->withHeader('Set-Cookie', "cart={$encodedCart}")
        ->withRedirect('/');

});


$app->run();

