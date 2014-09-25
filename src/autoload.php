<?php

class Autoloader {

    static public function loader($className) {

        $filename = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', '/', $className) . ".php";

        if (file_exists($filename)) {
            require $filename;

            if (class_exists($className)) {
                return true;
            }
        }

        return false;
    }
}

spl_autoload_register('Autoloader::loader');
