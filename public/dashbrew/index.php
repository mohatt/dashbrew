<?php
// Initialize the autoloader
require '/vagrant/src/autoload.php';

use Dashbrew\Dashboard\Application;
use Dashbrew\Dashboard\Controller\ControllerCollection;
use Dashbrew\Dashboard\View;

// Initialize the application
$app = new Application([
    'debug' => true,
    'view' => new View,
    'templates.path' => __DIR__ . '/views'
]);

$app->setName('Dashbrew');

$collection = new ControllerCollection($app, 'Dashbrew\Dashboard\Controllers');
$collection->addRoutes('home', ['/']);
$collection->addRoutes('projects');
$collection->addRoutes('server');

$app->run();
