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

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) use ($router) {
    $router->urlFor('users');
// Сюда как то прокинуть на конкретного юзера????????
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/users', function ($request, $response) use ($router) {
    $flash = $this->get('flash')->getMessages();		
    $users = json_decode($request->getCookieParam('users', json_encode([])), true);
    
    $params = [
        'users' => $users,
        'flash' => $flash
    ];
//    print_r($params);
    return $this->get('renderer')->render($response, "users/index.phtml", $params);
})->setName('users');

$app->get('/users/new', function ($request, $response) {
    $users = json_decode($request->getCookieParam('users', json_encode([])), true);	
    $params = [
        'user' => ['name' => '', 'sex' => ''],
        'errors' => []
    ];

    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->get('/users/{id}', function ($request, $response, array $args) use ($router) {
    $id = $args['id'];
    $users = json_decode($request->getCookieParam('users', json_encode([])), true);
    $idUsers = array_filter($users, function($user) {
        return $user['id'] === $id; 
    });

    if (!$user) {
        return $response->withStatus(404)->write('Page not found');
    }

    $params = [
	    'user' => $user,
	    'id' => $id
    ];

    print_r($id);

    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');

$app->get('/users/{id}/edit', function ($request, $response, array $args) use ($router) {
    $id = $args['id'];    
    $users = json_decode($request->getCookieParam('users', json_encode([])), true);
    $params = [
        'user' => $user,
        'errors' => [],
        //?????
        'route' => $router->urlFor('editUser', ['id' => $user['id']])
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);

})->setName('editUser');


$app->post('/users', function ($request, $response) use ($router) {
    // Извлекаем данные формы
    $user = $request->getParsedBodyParam('user');

    $validator = new Validator();
    $errors = $validator->validate($user);

    if (count($errors) === 0) {
    // Если данные коректны: сохр, доб флеш, редирект
        $users = json_decode($request->getCookieParam('users', json_encode([])), true);
	$users[] = $user;

	//Как сохранить?? 
	$encodedUsers = json_encode($users);

        $this->get('flash')->addMessage('success', 'User Added');
	return $response->withHeader('Set-Cookie', "users={$encodedUsers};Path=/users")
		->withRedirect('/users');
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];

    return $this->get('renderer')->render($response, 'users/new.phtml', $params);

});


$app->patch('/users/{id}', function ($request, $response, array $args) use ($router)  {
    $id = $args['id'];
    $userData = $request->getParsedBodyParam('user');
    $validator = new Validator();
    $errors = $validator->validate($userData);

    if (count($errors) === 0) {
        $users = json_decode($request->getCookieParam('users', json_encode([])), true);          
        $updatedUsers = array_map(function ($user) use ($userData, $id) {
            if ($user['id'] === $id) {
                return ['name' => $userData['name'], 'sex' => $userData['sex'], 'id' => $id];
            }
            return $user;
        }, $users);
        $encodedUsers = json_encode($updatedUsers);
	$this->get('flash')->addMessage('success', 'User has been updated');
	$route = $router->urlFor('editUser', ['id' => $user['id']]);
        return $response->withHeader('Set-Cookie', "users={$encodedUsers};Path=/users/{id}/edit")->withRedirect($route, 302);
    }

    $params = [
        'user' => $userData,
        'errors' => $errors
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});
/*


$app->delete('/users/{id}', function ($request, $response, array $args) use ($repo, $router) {
    $id = $args['id'];
    $repo->destroy($id);
    $this->get('flash')->addMessage('success', 'User has been deleted');
    return $response->withRedirect($router->urlFor('users'));
});
 */


$app->run();
