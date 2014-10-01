<?php

namespace Dashbrew\Dashboard;

/**
 * View Class
 *
 * @package Dashbrew\Dashboard
 */
class View extends \Slim\View {

    protected function render($template, $data = null) {

        return parent::render($template . '.php', $data);
    }
}
