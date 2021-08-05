<?php
if( !session_id() ) @session_start();
require '../vendor/autoload.php';

use League\Plates\Engine;
use \DI\ContainerBuilder;
use Delight\Auth\Auth;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    Engine::class => function() {
        return new Engine('../app/views');
    },

    PDO::class => function() {
        return new PDO("mysql:host=localhost;dbname=auth;charset=utf8", "root", "root");
    },

    
    Auth::class => function($container) {
        return new Auth($container->get('PDO'));
    },
    
]);
$container = $containerBuilder->build();


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', ['App\controllers\HomeController', 'index']);

    $r->addRoute('GET', '/registration', ['App\controllers\HomeController', 'registration']);

    $r->addRoute('POST', '/registrationForm', ['App\controllers\HomeController', 'registrationForm']);

    $r->addRoute('GET', '/login', ['App\controllers\HomeController', 'login']);
    
    $r->addRoute('POST', '/loginForm', ['App\controllers\HomeController', 'loginForm']);

    $r->addRoute('GET', '/logout', ['App\controllers\HomeController', 'logout']);

    $r->addRoute('GET', '/users', ['App\controllers\UsersController', 'index']);

    $r->addRoute('GET', '/addUser', ['App\controllers\UsersController', 'addUser']);

    $r->addRoute('POST', '/addUserForm', ['App\controllers\UsersController', 'addUserForm']);

    $r->addRoute('GET', '/edit/{id:\d+}', ['App\controllers\UsersController', 'edit']);

    $r->addRoute('POST', '/editForm', ['App\controllers\UsersController', 'editForm']);

    $r->addRoute('GET', '/security/{id:\d+}', ['App\controllers\UsersController', 'security']);

    $r->addRoute('POST', '/securityForm', ['App\controllers\UsersController', 'securityForm']);

    $r->addRoute('GET', '/status/{id:\d+}', ['App\controllers\UsersController', 'status']);

    $r->addRoute('POST', '/statusForm', ['App\controllers\UsersController', 'statusForm']);

    $r->addRoute('GET', '/media/{id:\d+}', ['App\controllers\UsersController', 'media']);

    $r->addRoute('POST', '/mediaForm', ['App\controllers\UsersController', 'mediaForm']);

    $r->addRoute('GET', '/delete/{id:\d+}', ['App\controllers\UsersController', 'delete']);
    
    // {id} must be a number (\d+)
    $r->addRoute('GET', '/user/{id:\d+}', ['App\controllers\HomeController', 'index']);
    // The /{title} suffix is optional
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});



// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo '404';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo '405';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2]; 
        $container->call($routeInfo[1], $routeInfo[2]);               
        break;
}

