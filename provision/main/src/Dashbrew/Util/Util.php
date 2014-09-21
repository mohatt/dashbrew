<?php

namespace Dashbrew\Util;

/**
 * Util Class.
 *
 * @package Dashbrew\Util
 */
class Util {

    /**
     * @return array
     */
    public static function getInstalledPhps() {

        $reg_key = 'installed_phps';
        if(!Registry::check($reg_key)){
            $phps = [];
            $finder = new Finder;
            $finder->directories()->in('/opt/phpbrew/php')->depth('== 0');
            foreach ($finder as $file) {
                $phps[] = $file->getFilename();
            }

            Registry::set($reg_key, $phps);
        }

        return Registry::get($reg_key);
    }

    public static function exec($command, &$output = null) {

        $lastLine = exec($command, $output, $returnValue);

        if ($returnValue != 0) {
            throw new \Exception("Command execution failed: $command\n" . implode("\n", $output));
        }

        return $lastLine;
    }

    public static function augeas($lns, $file, $key, $value) {

        $command = "augtool --autosave --noautoload --transform '$lns incl $file' set '/files/$file/$key' '$value'";

        self::exec($command, $output);

        $output = implode(" ", $output);
        if(false === strpos($output, 'Saved 1')){
            return false;
        }

        return true;
    }

    /**
     * @return \Symfony\Component\Yaml\Parser
     */
    public static function getYamlParser() {

        static $instance;

        if(!isset($instance)){
            $instance = new \Symfony\Component\Yaml\Parser;
        }

        return $instance;
    }

    /**
     * @return \Symfony\Component\Stopwatch\Stopwatch
     */
    public static function getStopwatch() {

        static $instance;

        if(!isset($instance)){
            $instance = new \Symfony\Component\Stopwatch\Stopwatch;
        }

        return $instance;
    }

    /**
     * @return Filesystem
     */
    public static function getFilesystem() {

        static $instance;

        if(!isset($instance)){
            $instance = new Filesystem;
        }

        return $instance;
    }
}
