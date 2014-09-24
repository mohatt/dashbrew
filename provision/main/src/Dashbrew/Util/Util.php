<?php

namespace Dashbrew\Util;
use Symfony\Component\Process\Process;

/**
 * Util Class.
 *
 * @package Dashbrew\Util
 */
class Util {

    /**
     * @param string $template
     * @param array $vars
     * @param bool $simple
     * @throws \Exception
     * @return string
     */
    public static function renderTemplate($template, array $vars = [], $simple = true) {

        $template = '/vagrant/provision/main/templates/' . ltrim($template, '/');
        if(!file_exists($template)){
            throw new \Exception("Unable to read template file '$template'");
        }

        if($simple){
            $s = [];
            $r = [];
            foreach($vars as $varname => $varvalue){
                $s[] = "{{ $varname }}";
                $r[] = strval($varvalue);
            }

            return str_replace($s, $r, file_get_contents($template));
        }

        $render_template = function() use($template, $vars) {
            ob_start();
            extract($vars);
            include($template);
            return ob_get_clean();
        };

        return $render_template();
    }

    /**
     * @return array
     */
    public static function getInstalledPhps() {

        static $phps;

        if(!isset($phps)){
            $phps = [];
            $finder = new Finder;
            $finder->directories()->in('/opt/phpbrew/php')->depth('== 0');
            foreach ($finder as $file) {
                $phps[] = $file->getFilename();
            }
        }

        return $phps;
    }

    public static function exec($command, $silent = false, &$output = null, &$return_var = null) {

        $output = null;
        $return_var = null;

        // Excute the command
        $last_line = exec($command, $output, $return_var);

        // Check if successfull
        if ($return_var != 0 && !$silent) {
            throw new \Exception("Command execution failed: $command\n" . implode("\n", $output));
        }

        return $return_var;
    }

    public static function process($command, $output, $force_output = false, $timeout = 60, $input = null, array $env = null, $cwd = null) {

        $process = new Process($command, null, $env, $input, $timeout);
        $process->run(function ($type, $buffer) use($output, $force_output) {
            if($type === Process::ERR){
                $output->writeStderr($buffer);
                return;
            }

            $buffer = trim($buffer, "\n");
            if(empty($buffer)){
                return;
            }

            if($output->isDebug() || $force_output){
                $output->writeStdout($buffer);
                return;
            }
        });

        if (!$process->isSuccessful()) {
            return false;
        }

        return true;
    }

    public static function augeas($lns, $file, $key, $value) {

        $command = "augtool --autosave --noautoload --transform '$lns incl $file' set '/files/$file/$key' '$value'";

        self::exec($command, false, $output);

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
