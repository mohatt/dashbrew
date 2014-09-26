<?php

class Autoload {

    static protected $namespaces = [
        'Pimple' => 'Pimple/lib',
        'Silex' => 'Silex/src',
    ];

    static public function loader($className) {

        $path = str_replace('\\', '/', $className);
        foreach(self::$namespaces as $ns => $nsDir){
            if($path == $ns || 0 === strpos($path, $ns . '/')){
                $path = $nsDir . '/' . $path;
                break;
            }
        }

        $filename = __DIR__ . DIRECTORY_SEPARATOR . $path . ".php";

        if (file_exists($filename)) {
            require $filename;

            if (class_exists($className)) {
                return true;
            }
        }

        return false;
    }
}

spl_autoload_register('Autoload::loader');
