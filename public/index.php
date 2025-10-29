<?php
require_once __DIR__ . '/../api/includes/Router.php';
require_once __DIR__ . '/../api/controllers/AuthController.php';
require_once __DIR__ . '/../api/controllers/TimeController.php';

$router = new Router();

$router->add('POST', '/api/register', [AuthController::class, 'register']);
$router->add('POST', '/api/login', [AuthController::class, 'login']);
$router->add('POST', '/api/decode', [AuthController::class, 'decode']);
$router->add("POST", '/api/timezone', [TimeController::class, 'addTimezone']);
$router->add("GET", '/api/timezone', [TimeController::class, 'loadTimezones']);
$router->add("DELETE", '/api/timezone/{tzName}', [TimeController::class, 'deleteTimezone']);

$router->dispatch();

?>