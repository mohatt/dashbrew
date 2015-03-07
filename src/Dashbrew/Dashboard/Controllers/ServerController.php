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

    protected function coderunner($type = null, $widget = null) {

        if($type != 'widget'){
            $this->set('layout_title', 'PHP Code Runner');
            $this->set('layout_breadcrumbs', ['controller' => 'Utilities']);
            return;
        }

        $this->setLayout('ajax');

        switch($widget){
            case 'input':
                $this->set('systemPhp', Util::getSystemPhp());
                $this->set('phps', $this->__getInstalledPhps());
                break;
            case 'output':
                $build = $this->request->post('version');
                $code = $this->request->post('code');
                if(!empty($build) && !empty($code)){
                    $output = Util::runPhpCode($code, $build);
                    $output = htmlspecialchars(implode("\n", $output));
                }
                else {
                    $output = "Click 'Run' to see PHP code output!";
                }

                $this->set('output', $output);
                break;
            default:
                throw new \Exception("Unknow widget type supplied");
        }

        $this->render('coderunner/' . $widget);
    }

    protected function phpinfo($version) {

        $this->setLayout('ajax');

        $output = Util::runPhpCode('<?php phpinfo(); ?>', $version);

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
        if(empty($phps)){
            return [];
        }

        $phpsInstalled = Util::getInstalledPhps();
        $default_build = null;
        foreach($phps as $build => $meta){
            if(!in_array($build, $phpsInstalled)){
                unset($phps[$build]);
                continue;
            }

            if(isset($meta['extensions'])){
                foreach($meta['extensions'] as $extname => $extmeta){
                    if(isset($extmeta['installed']) && !$extmeta['installed']){
                        unset($meta['extensions'][$extname]);
                    }
                }
            }

            $pidfile = '/opt/phpbrew/php/' . $build . '/var/run/php-fpm.pid';
            $phps[$build]['running'] = false;
            if(file_exists($pidfile) && trim(file_get_contents($pidfile)) != ""){
                $phps[$build]['running'] = true;
            }

            if($phps[$build]['default']){
                $default_build = $build;
            }

            $phps[$build]['default'] = false;
        }

        if($default_build){
            $phps[$default_build]['default'] = true;
        }

        return $phps;
    }
}
