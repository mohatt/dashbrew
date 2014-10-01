<?php

namespace Dashbrew\Dashboard\Controller;

use Slim\Slim;

/**
 * Controller Interface
 *
 * @package Dashbrew\Dashboard\Controller
 */
interface ControllerInterface {

    /**
     * @param Slim $app
     */
    public function __construct(Slim $app);

    /**
     * @param string $action
     * @param array $params
     * @return mixed
     */
    public function run($action, array $params = []);
}
