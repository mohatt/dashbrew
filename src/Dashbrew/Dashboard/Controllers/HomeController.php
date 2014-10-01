<?php

namespace Dashbrew\Dashboard\Controllers;

use Dashbrew\Dashboard\Controller\Controller;
use Dashbrew\Dashboard\Util\Stats;
/**
 * Home Controller Class
 *
 * @package Dashbrew\Dashboard\Controllers
 */
class HomeController extends Controller {

    protected function index() {

        $this->set('layout_title', 'Dashboard');
        $this->set('stats', [
          'phps'      => Stats::getPhpCount(),
          'projects'  => Stats::getProjectCount(),
          'databases' => Stats::getDatabaseCount(),
          'uptime'    => Stats::getUptime(),
        ]);

        //exec('uptime -s', $o);
        //var_dump($o);
/*
        //libfcgi0ldbl
        exec('SCRIPT_NAME=/test.php \
SCRIPT_FILENAME=/var/www/test.php \
DOCUMENT_ROOT=/var/www \
REQUEST_METHOD=GET \
cgi-fcgi -bind -connect 127.0.0.1:9002', $o);
        var_dump($o);
        die;
        */

         //var_dump($this->request->getPath());
         //var_dump($this->request->getRootUri());
    }
}
