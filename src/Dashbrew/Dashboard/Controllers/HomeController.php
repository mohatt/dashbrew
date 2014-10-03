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
    }

    protected function widget($type) {

        $this->setLayout('ajax');

        switch($type){
            case 'stats-projects':
                $this->set('count', Stats::getProjectCount());
                $this->set('icon', 'file-code-o');
                $this->set('comment', 'projects');
                break;
            case 'stats-databases':
                $this->set('count', Stats::getDatabaseCount());
                $this->set('icon', 'database');
                $this->set('comment', 'databases');
                break;
            case 'stats-phps':
                $this->set('count', Stats::getPhpsCount());
                $this->set('icon', 'code-fork');
                $this->set('comment', 'installed phps');
                break;
            case 'stats-uptime':
                $uptime = Stats::getUptime();
                if($uptime){
                    $uptime = implode(" ", $uptime);
                }
                $this->set('count', $uptime);
                $this->set('icon', 'clock-o');
                $this->set('comment', 'uptime');
                break;
            default:
                throw new \Exception("Unknow widget type supplied");
        }

        if(0 === strpos($type, 'stats-')){
            $this->render('widget/stats');
        }
    }
}
