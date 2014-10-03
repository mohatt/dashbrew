<?php

namespace Dashbrew\Dashboard\Controllers;

use Dashbrew\Dashboard\Controller\Controller;
use Dashbrew\Cli\Util\Util;
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

    protected function phpinfo($version) {

        $this->setLayout('ajax');

        if($version == 'system'){
            $fpmPort = '9001';
        }
        else {
            $phps = Config::get('php::builds');
            $phpsInstalled = Util::getInstalledPhps();
            if(empty($phps[$version]) || !in_array("php-{$version}", $phpsInstalled)){
                throw new \Exception("Unable to find php $version");
            }

            if(empty($phps[$version]['fpm']['port'])){
                throw new \Exception("Unable to find fpm port for php $version");
            }

            $fpmPort = $phps[$version]['fpm']['port'];
        }

        $scname = '/phpinfo.php';
        $scfname = $this->view->getTemplatePathname('controllers/server/phpinfo.php');
        $scroot = dirname($scfname);

        $cmd = 'SCRIPT_NAME=%s \
                SCRIPT_FILENAME=%s \
                DOCUMENT_ROOT=%s \
                REQUEST_METHOD=GET \
                cgi-fcgi -bind -connect 127.0.0.1:%s';

        $cmd = sprintf($cmd, $scname, $scfname, $scroot, $fpmPort);

        exec($cmd, $output, $return);

        if($return !== 0){
            throw new \Exception("Unable to connect to php-fpm at port $fpmPort");
        }

        foreach($output as $n => $line){
            if(0 === stripos($line, '<!doctype')){
                break;
            }

            unset($output[$n]);
        }

        echo implode("\n", $output);

        $this->rendered = true;
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
