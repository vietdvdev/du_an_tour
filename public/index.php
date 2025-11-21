<?php
declare(strict_types=1);

use App\Core\Env;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\ErrorHandler;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Config/constants.php';

Env::load(__DIR__ . '/../.env');
date_default_timezone_set(getenv('TIMEZONE') ?: 'Asia/Bangkok');

ErrorHandler::register();

$router = new Router();
require_once __DIR__ . '/../app/routes/web.php';

$request = Request::capture();
$response = $router->dispatch($request);
$response->send();
