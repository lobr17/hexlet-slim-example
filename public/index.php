<?php

use Slim\Factory\AppFactory;
use DI\Container;

use function Symfony\Component\String\s;

require __DIR__ . '/../vendor/autoload.php';

//session_start();

//$repo = new App\UserRepository();

$users = ['karet', 'mike', 'mishel', 'adel', 'keks', 'kamila', 'nhhffg'];

$container = new Container();
/*$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});*/
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

//AppFactory::setContainer($container);
//$app = AppFactory::create();
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/users', function ($request, $response) use ($users) {
//    $user = $request->getParsedBodyParam('user');	
    
    $term = $request->getQueryParam('term'); 
    $users = collect($users)->filter(
	    function($user) {
		    //   return empty($term) ? true : s($user['firstName'])->ignoreCase()->startsWith($term);
		return empty($term) ? true : strpost($user, $term);
	    } 
    );
    //$users = strpos($users, $term);

    $params = [
        'users' => $users,
        'term' => $term
    ];


    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->get('/users/new', function ($request, $response) {
    $params = [
       'user' => ['name' => '', 'secondName' => '']	
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml');

});

/*$app->get('/foo', function ($req, $res) {
    $this->get('flash')->addMessage('success', 'This is a message');
    return $res->withRedirect('/bar');
});

$app->get('/bar', function ($req, $res, $args) {
    $messages = $this->get('flash')->getMessages();
    print_r($messages);
});
 */
$app->run();

