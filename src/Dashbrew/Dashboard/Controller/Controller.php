<?php

namespace Dashbrew\Dashboard\Controller;

use Dashbrew\Dashboard\Application;
use Dashbrew\Dashboard\View;
use Dashbrew\Dashboard\Util\Inflector;
use Slim\Slim;
use Slim\Http\Request;
use Slim\Http\Response;
use Dashbrew\Cli\Util\Projects;

/**
 * Controller Class
 *
 * @package Dashbrew\Dashboard\Controller
 */
abstract class Controller implements ControllerInterface {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $vars = [];

    /**
     * @var string
     */
    protected $layout = 'default';

    /**
     * @var bool
     */
    protected $rendered = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(Slim $app){

        if(empty($this->name)){
            $this->__setControllerName();
        }

        $this->app          = $app;
        $this->view         = $this->app->view;
        $this->request      = $this->app->request;
        $this->response     = $this->app->response;

        $this->setViewHelpers();
    }

    /**
     * {@inheritdoc}
     */
    public function run($action, array $params = []){

        if(!method_exists($this, $action)){
            $controller = get_class($this);
            throw new \Exception("Undefined action $controller::$action");
        }

        $this->action = $action;

        call_user_func_array([$this, $action], $params);

        if(!$this->rendered){
            $this->render();
        }
    }

    /**
     * Adds useful helpers to the view class
     */
    protected function setViewHelpers(){

        $baseUrl          = $this->app->request->getRootUri();
        $baseUrlaAbsolute = $this->request->getUrl() . $baseUrl;

        $this->view->set('_asset', function($path, $absolute = false) use($baseUrl, $baseUrlaAbsolute){
            return ($absolute ? $baseUrlaAbsolute : $baseUrl) . '/assets/' . $path;
        });

        $this->view->set('_url', function($path, $absolute = false) use($baseUrl, $baseUrlaAbsolute){
            return ($absolute ? $baseUrlaAbsolute : $baseUrl) . $path;
        });
    }

    /**
     * Sets the default layout
     */
    protected function setLayout($layout){
        $this->layout = $layout;
    }

    /**
     * Renders actions output
     *
     * @param string|null $view
     * @throws \Exception
     */
    protected function render($view = null){

        if (empty($view) && !$this->rendered) {
            $view = $this->action;
        }

        $this->rendered = true;

        if (empty($view)) {
            $controller = get_class($this);
            throw new \Exception("Empty view path supplied to $controller::render");
        }

        $view = 'controllers/' . $this->name . '/' . $view;
        $this->vars = array_merge($this->vars, ['app' => $this->app]);
        $output = $this->view->fetch($view, (array) $this->vars);

        $layout_vars = [
          'app'         => $this->app,
          'content'     => $output,
        ];

        if(!empty($this->vars['layout_title'])){
            $layout_vars['layout_title'] = $this->vars['layout_title'];
        }
        else {
            $layout_vars['layout_title'] = Inflector::titleize($this->action);
        }

        $layout_vars['layout_breadcrumbs'] = [
          'app'        => $this->app->getName(),
          'controller' => Inflector::titleize($this->name),
        ];

        if($this->action != 'index'){
            $layout_vars['layout_breadcrumbs']['action'] = $layout_vars['layout_title'];
        }

        $layout_vars['layout_shortcuts'] = Projects::getShortcuts();

        if(!empty($this->vars['layout_breadcrumbs'])){
            $layout_vars['layout_breadcrumbs'] = array_merge(
              $layout_vars['layout_breadcrumbs'],
              (array) $this->vars['layout_breadcrumbs']
            );
        }

        $this->view->display('layouts/' . $this->layout, $layout_vars);
    }

    /**
     * Sets a variable to be passed to the view file
     *
     * @param string|array $varname
     * @param mixed $value
     */
    protected function set($varname, $value = null) {

        if(is_array($varname)){
            $this->vars = array_merge($this->vars, $varname);
            return;
        }

        $this->vars[$varname] = $value;
    }

    /**
     * Sets the name of the current controller.
     */
    private function __setControllerName(){

        $name = explode('\\', get_class($this));
        $name = array_pop($name);
        $name = substr($name, 0, strrpos($name, 'Controller'));

        $this->name = Inflector::underscore($name);
    }
}
