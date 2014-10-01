<?php

namespace Dashbrew\Dashboard\Controller;

use Slim\Slim;

/**
 * Controller Collection Interface
 *
 * @package Dashbrew\Dashboard\Controller
 */
interface ControllerCollectionInterface {

    /**
     * @param Slim $pp
     * @param string $ns
     */
    public function __construct(Slim $pp, $ns);

    /**
     * @param string $name
     * @param array $extraRoutes
     */
    public function addRoutes($name, array $extraRoutes = []);
}
