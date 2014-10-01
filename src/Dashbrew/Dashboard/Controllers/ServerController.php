<?php

namespace Dashbrew\Dashboard\Controllers;

use Dashbrew\Cli\Util\Util;
use Dashbrew\Dashboard\Controller\Controller;
use Dashbrew\Cli\Util\Config;

/**
 * Projects Controller Class
 *
 * @package Dashbrew\Dashboard\Controllers
 */
class ServerController extends Controller {

    protected function widget($type) {

        $this->setLayout('ajax');

        switch($type){
            case 'phps':
                $this->set('systemPhp', Util::getSystemPhp());
                $this->set('phps', $this->__getInstalledPhps());
                break;
            default:
                throw new \Exception("Unknow widget type supplied");
        }

        $this->render('widget/' . $type);
    }

    protected function __getInstalledPhps(){

        $phps = Config::get('php::builds');
        $phpsInstalled = Util::getInstalledPhps();
        foreach($phps as $version => $meta){
            if(!in_array("php-{$version}", $phpsInstalled)){
                unset($phps[$version]);
                continue;
            }

            if(isset($meta['extensions'])){
                foreach($meta['extensions'] as $extname => $extmeta){
                    if(isset($extmeta['installed']) && !$extmeta['installed']){
                        unset($meta['extensions'][$extname]);
                    }
                }
            }

            $pidfile = '/opt/phpbrew/php/php-' . $version . '/var/run/php-fpm.pid';
            $phps[$version]['running'] = false;
            if(trim(file_get_contents($pidfile)) != ""){
                $phps[$version]['running'] = true;
            }
        }

        return $phps;
    }
}
