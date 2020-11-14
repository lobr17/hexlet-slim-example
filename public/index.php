<?php

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use App\Validator;
//use function Symfony\Component\String\s;

require __DIR__ . '/../vendor/autoload.php';

session_start();

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

$repo = new App\UserRepository();
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) use ($router) {
    $router->urlFor('users');

    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/users', function ($request, $response) use ($repo) {
    $flash = $this->get('flash')->getMessages();		
  
    $params = [
        'users' => $repo->all(),
        'flash' => $flash
    ];
    return $this->get('renderer')->render($response, "users/index.phtml", $params);
})->setName('users');

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['name' => '', 'sex' => ''],
        'errors' => []
    ];

    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});


$app->get('/users/{id}', function ($request, $response, array $args) use ($repo) {
    $id = $args['id'];
    $user = $repo->find($id);

    if (!$user) {
        return $response->withStatus(404)->write('Page not found');
    }

    $params = [
        'user' => $user
    ];

    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');


$app->get('/users/{id}/edit', function ($request, $response, array $args) use ($repo) {
    $id = $args['id'];
    $user = $repo->find($id);
    $params = [
        'user' => $user,
	'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);

})->setName('editUser');

$app->post('/users', function ($request, $response) use ($repo, $router) {
    // Извлекаем данные формы
    $user = $request->getParsedBodyParam('user');

    $validator = new Validator();
    $errors = $validator->validate($user);

    if (count($errors) === 0) {
    // Если данные коректны: сохр, доб флеш, редирект
        $repo->save($user);
	$this->get('flash')->addMessage('success', 'User Added');
	$url = $router->urlFor('users');
        return $response->withRedirect($url);
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);

});

$app->patch('/users/{id}', function ($request, $response, array $args) use ($repo, $router)  {
    $id = $args['id'];
    $user = $repo->find($id);
    $data = $request->getParsedBodyParam('user');

    $validator = new Validator();
    $errors = $validator->validate($data);

    if (count($errors) === 0) {
        // Ручное копирование данных из формы в нашу сущность
        $user['name'] = $data['name'];

        $this->get('flash')->addMessage('success', 'School has been updated');
        $repo->save($user);
        $url = $router->urlFor('editUser', ['id' => $user['id']]);
        return $response->withRedirect($url);
    }

    $params = [
        'userData' => $data,
        'user' => $user,
        'errors' => $errors
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});

$app->run();

