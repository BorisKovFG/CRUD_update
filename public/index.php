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
    $params = [
        'school' => $school
    ];
    $url = $router->urlFor("editSchool", ['id' => $school['id']]);
    $response = $response->write("<a href=$url>Edit name of School by id</a>");
    return $this->get('renderer')->render($response, "schools/index.phtml", $params);
})->setName("schools");

$app->get("/schools/{id}/edit", function ($request, $response, array $args) use ($repo) {
    $id = $args['id'];
    $school = $repo->find($id);
    $flash = $this->get('flash')->getMessages();
    $params = [
        'errors' => [],
        'school' => $school,
        'flash' => $flash
    ];
    return $this->get('renderer')->render($response, 'schools/edit.phtml', $params);
})->setName("editSchool");

$app->patch("/schools/{id}", function ($request, $response, array $args) use ($repo, $router) {
    $id = $args['id'];
    $dataFromDb = $repo->find($id);
    $dataFromForm = $request->getParsedBodyParam('school');

    $validator = new \App\Validator();
    $errors = $validator->validate($dataFromForm);

    if (count($errors) === 0) {
        $dataFromDb['name'] = $dataFromForm['name'];
        $repo->save($dataFromDb); // save data which was from db, because we edit only name
        $this->get('flash')->addMessage("success", "Name was edit");
        $url = $router->urlFor("editSchool", ['id' => $dataFromDb['id']]);
        return $response->withRedirect($url);
    }

    $params = [
        'school' => $dataFromDb,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response->withStatus(422), "/schools/edit.phtml", $params);
});
$app->run();
