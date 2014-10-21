<?php

namespace Dashbrew\Cli\Util;

/*
 * @todo refactor this
 */
class ServiceManager extends Registry {

    private static $key = 'services';

    public static function addService($service) {

        if(!self::check(self::$key)){
            self::set(self::$key, []);
        }

        $services = self::get(self::$key);
        if(in_array($service, $services)){
            return;
        }

        $services[] = $service;
        self::set(self::$key, $services);
    }

    public static function getServices() {

        if(!self::check(self::$key)){
            return [];
        }

        return self::get(self::$key);
    }
}
