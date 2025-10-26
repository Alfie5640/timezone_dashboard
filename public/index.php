<?php
require_once __DIR__ . '/../api/includes/Router.php';
require_once __DIR__ . '/../api/controllers/AuthController.php';

$router = new Router();

$router->add('POST', '/api/register', [AuthController::class, 'register']);
$router->add('POST', '/api/login', [AuthController::class, 'login']);
$router->add('POST', '/api/decode', [AuthController::class, 'decode']);

$router->dispatch();

?>