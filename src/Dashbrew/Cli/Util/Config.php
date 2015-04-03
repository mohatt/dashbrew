<?php

namespace Dashbrew\Cli\Util;

/**
 * Config Class.
 *
 * @package Dashbrew\Util
 */
class Config {

    /**
     * The path to the main config file
     */
    const CONFIG_FILE       = '/vagrant/config/environment.yaml';

    /**
     * The path to the previous version of the config file, it is being used to
     *  detect deleted config enteries
     */
    const CONFIG_FILE_OLD   = '/vagrant/provision/main/etc/environment.yaml.old';

    /**
     * Default config values
     *
     * @var array
     */
    protected static $defaults = [
      'os::packages'      => [],
      'php::builds'       => [],
      'apache::modules'   => [],
      'npm::packages'     => [],
      'debug'             => false,
    ];

    /**
     * @var array
     */
    protected static $config;

    /**
     * @var array
     */
    protected static $configOld;

    /**
     * @param bool $mergeOld
     * @throws \Exception
     */
    public static function init($mergeOld = true) {

        $yaml = Util::getYamlParser();

        self::$config = self::$defaults;
        if(file_exists(self::CONFIG_FILE)){
            $configYaml = $yaml->parse(file_get_contents(self::CONFIG_FILE));
            self::$config = array_merge(self::$config, $configYaml);
        }

        self::$configOld = self::$defaults;
        if(file_exists(self::CONFIG_FILE_OLD)){
            $configOldYaml = $yaml->parse(file_get_contents(self::CONFIG_FILE_OLD));
            self::$configOld = array_merge(self::$configOld, $configOldYaml);
        }

        if($mergeOld){
            self::mergeOldConfig();
        }

        self::validateConfig();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get($key = null) {

        if(!isset(self::$config)){
            self::init();
        }

        if($key !== null){
            return self::$config[$key];
        }

        return self::$config;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function getOld($key = null) {

        if(!isset(self::$configOld)){
            self::init();
        }

        if($key !== null){
            return self::$configOld[$key];
        }

        return self::$configOld;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function hasChanges($key = null) {

        if(!isset(self::$configOld)){
            self::init();
        }

        if(isset($key)){
            return self::$config[$key] !== self::$configOld[$key];
        }

        return self::$config !== self::$configOld;
    }

    /**
     *
     */
    public static function writeOld() {

        $fs = Util::getFilesystem();
        if(!file_exists(self::CONFIG_FILE)){
            $fs->remove(self::CONFIG_FILE_OLD);
            return;
        }

        $fs->copy(self::CONFIG_FILE, self::CONFIG_FILE_OLD, true);
    }

    /**
     *
     */
    protected static function validateConfig() {

        //@todo add more in-depth validation and throw exceptions when needed

        foreach(self::$config as $key => $value){
            switch($key){
                case 'os::packages':
                case 'php::builds':
                case 'apache::modules':
                case 'npm::packages':
                    self::$config[$key] = (array) self::$config[$key];
                    self::$configOld[$key] = (array) self::$configOld[$key];
                    break;
                case 'debug':
                    self::$config[$key] = (bool) self::$config[$key];
                    self::$configOld[$key] = (bool) self::$configOld[$key];
                    break;
            }
        }
    }

    /**
     *
     */
    protected static function mergeOldConfig() {

        if(self::$config === self::$configOld){
            return;
        }

        foreach(self::$configOld as $mkey => $mvalue){
            if(!is_array($mvalue)){
                continue;
            }

            foreach($mvalue as $key => $value){
                if(isset(self::$config[$mkey][$key])){
                    continue;
                }

                switch($mkey){
                    case 'os::packages':
                        if($value){
                            self::$config[$mkey][$key] = false;
                        }
                        break;
                    case 'php::builds':
                        if(!isset($value['installed']) || $value['installed']){
                            self::$config[$mkey][$key]['installed'] = false;
                        }
                        break;
                    case 'apache::modules':
                        if($value){
                            self::$config[$mkey][$key] = false;
                        }
                        break;
                    case 'npm::packages':
                        if($value){
                            self::$config[$mkey][$key] = false;
                        }
                        break;
                }
            }
        }
    }
}
