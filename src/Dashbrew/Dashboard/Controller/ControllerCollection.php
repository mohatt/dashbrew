<?php

namespace Dashbrew\Dashboard\Controller;

use Slim\Slim;
use Dashbrew\Dashboard\Util\Inflector;

/**
 * Controller Collection Class
 *
 * @package Dashbrew\Dashboard\Controller
 */
class ControllerCollection implements ControllerCollectionInterface {

    /**
     * @var Slim
     */
    protected $app;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @param Slim $app
     * @param string $ns
     */
    public function __construct(Slim $app, $ns){

        $this->app = $app;
        $this->namespace = $ns;
    }

    /**
     * @param string $name
     * @param array $extraRoutes
     */
    public function addRoutes($name, array $extraRoutes = []){

        $cb = [$this, $name];

        $this->app->map("/$name", $cb)->via('GET', 'POST');
        $this->app->map("/$name/:action", $cb)->via('GET', 'POST');
        $this->app->map("/$name/:action/:params+", $cb)->via('GET', 'POST');

        if(!empty($extraRoutes)){
            foreach($extraRoutes as $route){
                $this->app->map($route, $cb)->via('GET', 'POST');
            }
        }
    }

    /**
     * @param string $name
     * @param array $args
     * @throws \Exception
     */
    public function __call($name , array $args = []){

        $className = $this->getControllerClassName($name);
        if(!class_exists($className)){
            throw new \Exception("Unable to find controller controller class '$className'");
        }

        $obj = new $className($this->app);
        if(!$obj instanceof ControllerInterface){
            throw new \Exception("Controller class '$className' must implement ControllerInterface");
        }

        $action = 'index';
        $params = [];

        if(isset($args[0])){
            $action = $args[0];
        }

        if(isset($args[1])){
            $params = $args[1];
        }

        $obj->run($action, $params);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getControllerClassName($name){

        $className = Inflector::camelize($name) . 'Controller';
        return $this->namespace . '\\' . $className;
    }
}
