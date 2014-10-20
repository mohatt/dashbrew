<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\Config;
use Dashbrew\Cli\Util\SyncManager;
use Dashbrew\Cli\Util\ServiceManager;

/**
 * Php Task Class
 *
 * Manages php versions and their configurations
 *
 * @package Dashbrew\Cli\Tasks
 */
class PhpTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Php task can only be run by the Provision command.");
        }

        $phps = Config::get('php::builds');
        if(empty($phps)){
            return;
        }

        $phpsOld = Config::getOld('php::builds');
        $installedPhps = Util::getInstalledPhps();
        $default_php = null;

        foreach($phps as $build => $meta) {

            $meta['_build'] = $build;
            $meta['_path'] = "/opt/phpbrew/php/$build";
            $meta['installed'] = (!isset($meta['installed']) || $meta['installed']);
            $meta['_is_installed'] = in_array($build, $installedPhps);
            $meta['_old'] = isset($phpsOld[$build]) ? $phpsOld[$build] : [];

            // ignore if php to be removed and is not installed
            if(!$meta['installed'] && !$meta['_is_installed']){
                continue;
            }

            $this->managePhp($meta);
            $this->manageExtensions($meta);
            $this->manageFpm($meta);

            if(!empty($meta['default'])){
                $default_php = $build;
            }
        }

        $this->setDefaultPhp($default_php);
    }

    /**
     * Manages php builds via phpbrew
     *
     * @param $meta
     * @throws \Exception
     */
    protected function managePhp($meta) {

        $fs = Util::getFilesystem();
        $this->output->writeInfo("Checking php $meta[_build]");

        if(!$meta['installed']){
            $this->output->writeInfo("Removing php");
            $this->stopFpm($meta['_build']);
            $proc = $this->runScript('php.remove', $meta['_build']);
            if($proc->isSuccessful()){
                $this->output->writeInfo("Successfully removed php");
            }
            else {
                throw new \Exception("Unable to remove php");
            }

            SyncManager::removeRule($meta['_path'], true, true);
            $fs->remove($meta['_path']);
            return;
        }

        if(!isset($meta['version'])){
            $meta['version'] = $meta['_build'];
        }

        if(!empty($meta['_old']) && !isset($meta['_old']['version'])){
            $meta['_old']['version'] = $meta['_build'];
        }

        if(!preg_match('/^[0-9][0-9\.]*[0-9]$/', $meta['version'])){
            throw new \Exception("Invalid php version $meta[version]");
        }

        if(version_compare($meta['version'], '5.3.0') < 0){
            throw new \Exception("Building php versions older than 5.3.0 is not supported");
        }

        if(empty($meta['variants'])){
            throw new \Exception("Build variants for php $meta[_build] are not defined in config.yaml file");
        }

        if($meta['_is_installed'] && !empty($meta['_old']) && $meta['_old']['version'] == $meta['version'] && $meta['_old']['variants'] == $meta['variants']){
            return;
        }

        $this->output->writeInfo("Building php from source");
        $this->output->writeInfo("This may take a while depending on your cpu(s)...");
        $proc = $this->runScript('php.install', $meta['_build'], $meta['version'], $meta['variants']);
        if($proc->isSuccessful()){
            $this->output->writeInfo("Successfully built php");
            // Get a copy of the log file
            $log_from = "/opt/phpbrew/build/php-$meta[version]/build.log";
            $log_to = "/vagrant/provision/main/logs/phpbuild-$meta[_build].log";
            $fs->copy($log_from, $log_to, true);
            $this->output->writeInfo("Saved build log file to $log_to");
        }
        else {
            $fs->remove($meta['_path']);
            throw new \Exception("Unable to build php");
        }
    }

    /**
     * Manages php extentions via phpbrew
     *
     * @param $meta
     * @throws \Exception
     */
    protected function manageExtensions($meta) {

        if(!isset($meta['extensions'])){
            return;
        }

        $this->output->writeInfo("Checking extensions");

        // skip if php is to be removed
        if(!$meta['installed']){
            return;
        }

        foreach ($meta['extensions'] as $extname => $extmeta) {
            if(empty($extmeta['version'])){
                $this->output->writeError("Invalid extension definition for $extname extension, missing 'version' paramater");
                continue;
            }

            if(!isset($extmeta['enabled'])){
                $extmeta['enabled'] = true;
            }

            $ini = $meta['_path'] . "/var/db/$extname.ini";
            $ini_disabled = $meta['_path'] . "/var/db/$extname.ini.disabled";

            $ext_installed = file_exists($ini) || file_exists($ini_disabled);
            if(!$ext_installed || (isset($meta['_old']['extensions'][$extname]['version']) && $meta['_old']['extensions'][$extname]['version'] !== $extmeta['version'])){
                $this->output->writeInfo("Installing $extname extension");
                $proc = $this->runScript('ext.install', $meta['_build'], $extname, $extmeta['version']);
                if($proc->isSuccessful()){
                    $this->output->writeInfo("Successfully installed $extname extension");
                }
                else {
                    $this->output->writeError("Failed installing $extname extension");
                    continue;
                }
            }

            $ext_enabled = file_exists($ini);
            if($extmeta['enabled'] && !$ext_enabled){
                $this->output->writeInfo("Enabling $extname extension");
                $proc = $this->runScript('ext.enable', $meta['_build'], $extname);
                if($proc->isSuccessful()){
                    $this->output->writeInfo("Successfully enabled $extname extension");
                }
                else {
                    $this->output->writeError("Failed enabling $extname extension");
                }
            }

            if (!$extmeta['enabled'] && $ext_enabled){
                $this->output->writeInfo("Disabling $extname extension");
                $proc = $this->runScript('ext.disable', $meta['_build'], $extname);
                if($proc->isSuccessful()){
                    $this->output->writeInfo("Successfully disabled $extname extension");
                }
                else {
                    $this->output->writeError("Failed disabling $extname extension");
                }
            }
        }
    }

    /**
     * Manages php fpm via augeas and monit
     *
     * @param $meta
     * @throws \Exception
     */
    protected function manageFpm($meta) {

        if(empty($meta['fpm']['port']) && empty($meta['_old']['fpm']['port'])){
            return;
        }

        $this->output->writeInfo("Checking fpm");

        $fs = Util::getFilesystem();
        $monit_conf_file = "/etc/monit/conf.d/php-$meta[_build]-fpm.conf";
        $apache_conf_file = "/etc/apache2/php/php-$meta[_build]-fpm.conf";

        if(empty($meta['fpm']['port']) || !$meta['installed']){
            if(!empty($meta['_old']['fpm']['port'])){
                $this->stopFpm($meta['_build']);
            }

            if(file_exists($monit_conf_file)){
                $this->output->writeInfo("Removing monit php-fpm config file '$monit_conf_file'");
                $fs->remove($monit_conf_file);
            }

            if(file_exists($apache_conf_file)){
                $this->output->writeInfo("Removing apache php-fpm config file '$apache_conf_file'");
                $fs->remove($apache_conf_file);
            }

            return;
        }

        $fpm_config_file = $meta['_path'] . '/etc/php-fpm.conf';
        $fpm_config_updated_1 = Util::augeas('PHP', $fpm_config_file, 'www/listen', '127.0.0.1:' . $meta['fpm']['port']);
        $fpm_config_updated_2 = Util::augeas('PHP', $fpm_config_file, 'www/user', 'www-data');
        $fpm_config_updated_3 = Util::augeas('PHP', $fpm_config_file, 'www/group', 'www-data');
        if(0 === $fpm_config_updated_1 || 0 === $fpm_config_updated_2 || 0 === $fpm_config_updated_3){
            $this->output->writeError("Failed to configure fpm config file '$fpm_config_file'");
        }
        else if(2 === $fpm_config_updated_1 || 2 === $fpm_config_updated_2 || 2 === $fpm_config_updated_3){
            $this->output->writeInfo("Configured fpm");
        }

        $monit_conf_template = Util::renderTemplate('monit/conf.d/php-fpm.conf.php', [
            'build'   => $meta['_build'],
            'port'    => $meta['fpm']['port'],
        ]);

        if(!file_exists($monit_conf_file) || md5($monit_conf_template) !== md5_file($monit_conf_file)){
            $this->output->writeInfo("Writing monit php-fpm config file '$monit_conf_file'");
            $fs->write($monit_conf_file, $monit_conf_template, 'root');
        }

        $apache_conf_template = Util::renderTemplate('apache/php/php-fpm.conf.php', [
            'build'   => $meta['_build'],
            'port'    => $meta['fpm']['port'],
        ]);

        if(!file_exists($apache_conf_file) || md5($apache_conf_template) !== md5_file($apache_conf_file)){
            $this->output->writeInfo("Writing apache php-fpm config file '$apache_conf_file'");
            $fs->write($apache_conf_file, $apache_conf_template, 'root');
        }

        if(!isset($meta['fpm']['autostart']) || $meta['fpm']['autostart']){
            ServiceManager::addService("php-$meta[_build]-fpm");
        }
    }

    /**
     * Stops php-fpm
     *
     * @param $build
     * @throws \Exception
     */
    protected function stopFpm($build) {

        $this->output->writeInfo("Stopping php-fpm service");
        $proc = Util::process($this->output, "monit stop php-$build-fpm");
        if($proc->isSuccessful()){
            // wait until monit stops the service
            while(file_exists("/opt/phpbrew/php/$build/var/run/php-fpm.pid")) usleep(500000);
        }
    }

    /**
     * Sets default php version
     *
     * @param $build
     * @throws \Exception
     */
    protected function setDefaultPhp($build) {

        $proc = $this->runScript('php.current');
        if(!$proc->isSuccessful()){
            $this->output->writeError("Failed to get current php version");
            return;
        }

        $current = null;
        $output = $proc->getOutput();
        if(preg_match('/using\s(.*)/i', $output, $matches)){
            $current = $matches[1];
        }

        if($current === $build){
            return;
        }

        // Use system php
        if(null === $build){
            $proc = $this->runScript('php.switchoff');
            if(!$proc->isSuccessful()){
                $this->output->writeError("Unable to switch off phpbrew");
            }

            return;
        }

        $proc = $this->runScript('php.switch', $build);
        if(!$proc->isSuccessful()){
            $this->output->writeError("Unable to switch to php $build");
        }
    }

    /**
     * Runs a phpbrew shell script
     *
     * @return \Symfony\Component\Process\Process
     * @throws \Exception
     */
    protected function runScript() {

        $args = func_get_args();
        $args_size = func_num_args();
        if($args_size == 0){
            throw new \Exception("runScript() function requires at lease one argument, $args_size was given");
        }

        $script = array_shift($args);
        $cmd = "/vagrant/provision/main/scripts/phpbrew/$script.sh";
        if(count($args) > 0){
            $cmd .= " " . implode(" ", $args);
        }

        $proc = Util::Process($this->output, "sudo -u vagrant bash $cmd", ['timeout' => null]);

        return $proc;
    }
}
