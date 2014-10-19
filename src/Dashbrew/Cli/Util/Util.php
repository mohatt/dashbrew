<?php

namespace Dashbrew\Cli\Util;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;

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

    public static function runPhpCode($code, $build = 'system'){

        if($build == 'system'){
            $fpmPort = '9001';
        }
        else {
            $phps = Config::get('php::builds');
            $phpsInstalled = Util::getInstalledPhps();
            if(empty($phps[$build]) || !in_array($build, $phpsInstalled)){
                throw new \Exception("Unable to find php $build");
            }

            if(empty($phps[$build]['fpm']['port'])){
                throw new \Exception("Unable to find fpm port for php $build");
            }

            $fpmPort = $phps[$build]['fpm']['port'];
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
     * @param array $options Process options, includes:
     *  bool logfile
     *  bool stderr
     *  bool stdout
     *  int timeout
     *  string input
     *  array env
     *  string cwd
     * @return Process
     */
    public static function process(OutputInterface $output, $command, array $options = []) {

        $options = array_merge([
          'stderr'  => true,
          'stdout'  => $output->isDebug(),
          'timeout' => 60,
          'input'   => null,
          'env'     => null,
          'cwd'     => null,
          'logfile' => null, //@todo implement process logger
        ], $options);

        $output->writeDebug(str_repeat('-', 55));
        $output->writeDebug("Executing command: $command");
        $output->writeDebug(str_repeat('-', 55));

        $process = new Process($command, $options['cwd'], $options['env'], $options['input'], $options['timeout']);
        $process->run(function ($type, $buffer) use($output, $options) {
            if($type === Process::ERR){
                if($options['stderr']){
                    $output->writeStderr($buffer);
                }

                return;
            }

            if(!$options['stdout']){
                return;
            }

            $buffer = trim($buffer, "\n");
            if(empty($buffer)){
                return;
            }

            $output->writeStdout($buffer);
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
