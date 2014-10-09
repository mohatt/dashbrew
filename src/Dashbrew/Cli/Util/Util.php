<?php

namespace Dashbrew\Cli\Util;

use Symfony\Component\Process\Process;
use Dashbrew\Cli\Output\OutputInterface;

/**
 * Util Class.
 *
 * @package Dashbrew\Cli\Util
 */
class Util {

    /**
     * @param string $template
     * @param array $vars
     * @throws \Exception
     * @return string
     */
    public static function renderTemplate($template, array $vars = []) {

        $template = '/vagrant/provision/main/templates/' . ltrim($template, '/');
        if(!file_exists($template)){
            throw new \Exception("Unable to read template file '$template'");
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
                $phpbin = $file->getPathname() . '/bin/php';
                if(!file_exists($phpbin)){
                    continue;
                }

                $phps[] = $file->getFilename();
            }
        }

        return $phps;
    }

    /**
     * @return string
     */
    public static function getSystemPhp() {

        $command = '/usr/bin/php -v | grep "PHP 5" | sed "s/.*PHP \([^-]*\).*/\1/" | cut -c 1-6';

        if(!self::exec($command, false, $output)){
            return 0;
        }

        return $output[0];
    }

    public static function runPhpCode($code, $version = 'system'){

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

        $filename = uniqid('coderunner_', true) . '.php';
        $filepath = '/tmp/' . $filename;

        $fs = self::getFilesystem();
        $fs->touch($filepath);
        $fs->chmod($filepath, 0777);
        $fs->write($filepath, $code);

        $scname   = '/' . $filename;
        $scfname  = $filepath;
        $scroot   = dirname($scfname);

        $cmd = 'SCRIPT_NAME=%s \
                SCRIPT_FILENAME=%s \
                DOCUMENT_ROOT=%s \
                REQUEST_METHOD=GET \
                cgi-fcgi -bind -connect 127.0.0.1:%s';

        $cmd = sprintf($cmd, $scname, $scfname, $scroot, $fpmPort);

        self::exec($cmd, false, $output);

        $fs->remove($filepath);

        return $output;
    }

    /**
     * @param string $command
     * @param bool $silent
     * @param null $output
     * @param null $return_var
     * @return bool
     * @throws \Exception
     */
    public static function exec($command, $silent = false, &$output = null, &$return_var = null) {

        $output = null;
        $return_var = null;

        // Excute the command
        exec($command, $output, $return_var);

        // Check if successfull
        if ($return_var !== 0 && !$silent) {
            throw new \Exception("Command execution failed: $command\n Stdout:" . implode("\n", $output));
        }

        return 0 === $return_var;
    }

    /**
     * @param string $command
     * @param OutputInterface $output
     * @param bool $disable_stderr
     * @param null|bool $force_stdout can be set to `true` to enable stdout, `false` to
     *  disable stdout or `null` to send stdout according to current verbosity level.
     * @param int $timeout
     * @param null $input
     * @param array $env
     * @param null $cwd
     * @return Process
     */
    public static function process($command, OutputInterface $output, $disable_stderr = false, $force_stdout = null, $timeout = 60, $input = null, array $env = null, $cwd = null) {

        $output->writeDebug(str_repeat('-', 55));
        $output->writeDebug("Executing command: $command");
        $output->writeDebug(str_repeat('-', 55));

        $process = new Process($command, null, $env, $input, $timeout);
        $process->run(function ($type, $buffer) use($output, $disable_stderr, $force_stdout) {
            if($type === Process::ERR){
                if(true !== $disable_stderr){
                    $output->writeStderr($buffer);
                }

                return;
            }

            $buffer = trim($buffer, "\n");
            if(empty($buffer) || false === $force_stdout){
                return;
            }

            if($output->isDebug() || true === $force_stdout){
                $output->writeStdout($buffer);
                return;
            }
        });

        $output->writeDebug(str_repeat('-', 55));

        return $process;
    }

    /**
     * @param string $lns
     * @param string $file
     * @param string $key
     * @param string $value
     * @return int
     * @throws \Exception
     */
    public static function augeas($lns, $file, $key, $value) {

        $command = "augtool --autosave --noautoload --transform '$lns incl $file' set '/files/$file/$key' '$value'";

        if(!self::exec($command, false, $output)){
            return 0;
        }

        $output = implode(" ", $output);
        if(false !== stripos($output, 'saved 1')){
            return 2;
        }

        return 1;
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
