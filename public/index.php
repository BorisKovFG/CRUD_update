<?php


use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware; //for changing method from post to patch
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

//init App with requires
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

//for flash messages
session_start();
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

//names for routing
$router = $app->getRouteCollector()->getRouteParser();

//db for data
$repo = new App\SchoolRepository();

//for changing method from post to patch
$app->add(MethodOverrideMiddleware::class);

$app->get("/" , function ($request, $response) use ($router) {
    $url = $router->urlFor("schools");
    $response = $response->write("<a href=$url>List of Shcools</a>");
    return $this->get('renderer')->render($response, "index.phtml");
})->setName("main");

$app->get("/schools", function ($request, $response) use ($router, $repo) {
    $school = $repo->read();
    $url = $router->urlFor("editSchool");
    $params = [
        'school' => $school
    ];
    $url = "schools/{$school['id']}/edit";
    $response = $response->write("<a href=$url>Edit name of School by id</a>");
    return $this->get('renderer')->render($response, "schools/index.phtml", $params);
})->setName("schools");

$app->get("schools", function ($request, $response) {
    return $this->get('renderer')->render($response, 'schools/edit.phtml');
})->setName("editSchool");
$app->run();
