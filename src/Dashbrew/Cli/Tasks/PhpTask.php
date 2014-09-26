<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\Config;

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
            throw new \Exception("The PHP task can only be run by the Provision command.");
        }

        $phps = Config::get('php::builds');
        $phpsOld = Config::getOld('php::builds');
        $installedPhps = Util::getInstalledPhps();
        $default_php = null;

        foreach($phps as $version => $meta) {

            $meta['_version'] = $version;
            $meta['_path'] = "/opt/phpbrew/php/php-$version";
            $meta['_is_installed'] = in_array('php-' . $version, $installedPhps);
            $meta['_old'] = isset($phpsOld[$version]) ? $phpsOld[$version] : [];

            $this->managePhp($meta);
            $this->manageExtensions($meta);
            $this->manageFpm($meta);

            if(!empty($meta['default'])){
                $default_php = $version;
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
        $this->output->writeInfo("Checking php $meta[_version]");

        if(isset($meta['installed']) && !$meta['installed']){
            if(!$meta['_is_installed']){
                return;
            }

            $this->output->writeInfo("Removing php");
            $proc = $this->runScript('php.remove', true, $meta['_version']);
            if($proc->isSuccessful()){
                $this->output->writeInfo("Successfully removed php");
            }
            else {
                throw new \Exception("Unable to remove php");
            }

            $fs->remove($meta['_path']);
            return;
        }

        if(!isset($meta['variants'])){
            throw new \Exception("Build variants for php $meta[_version] are not defined in config.yaml file");
        }

        if($meta['_is_installed'] && isset($meta['_old']['variants']) && $meta['_old']['variants'] === $meta['variants']){
            return;
        }

        $this->output->writeInfo("Building php");
        $proc = $this->runScript('php.install', true, $meta['_version'], $meta['variants']);
        if($proc->isSuccessful()){
            $this->output->writeInfo("Successfully built php");
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
        if(isset($meta['installed']) && !$meta['installed']){
            return;
        }

        foreach ($meta['extensions'] as $extname => $extmeta) {

            $ini = $meta['_path'] . "/var/db/$extname.ini";
            $ini_disabled = $meta['_path'] . "/var/db/$extname.ini.disabled";

            $ext_installed = file_exists($ini) || file_exists($ini_disabled);
            if(!$ext_installed || (isset($meta['_old']['extensions'][$extname]['version']) && $meta['_old']['extensions'][$extname]['version'] !== $extmeta['version'])){
                $this->output->writeInfo("Installing $extname extension");
                $proc = $this->runScript('ext.install', true, $meta['_version'], $extname);
                if($proc->isSuccessful()){
                    $this->output->writeInfo("Successfully installed $extname extension");
                }
                else {
                    throw new \Exception("Unable to install $extname extension");
                }
            }

            $ext_enabled = file_exists($ini);
            if($extmeta['enabled'] && !$ext_enabled){
                $this->output->writeInfo("Enabling $extname extension");
                $proc = $this->runScript('ext.enable', null, $meta['_version'], $extname);
                if($proc->isSuccessful()){
                    $this->output->writeInfo("Successfully enabled $extname extension");
                }
                else {
                    throw new \Exception("Unable to enable $extname extension");
                }
            }

            if (!$extmeta['enabled'] && $ext_enabled){
                $this->output->writeInfo("Disabling $extname extension");
                $proc = $this->runScript('ext.disable', null, $meta['_version'], $extname);
                if($proc->isSuccessful()){
                    $this->output->writeInfo("Successfully disabled $extname extension");
                }
                else {
                    throw new \Exception("Unable to disable $extname extension");
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

        if(empty($meta['fpm']['port']) && empty($meta['_old']['fpm']['port']) ){
            return;
        }

        $this->output->writeInfo("Checking fpm");

        $fs = Util::getFilesystem();
        $monit_conf_file = "/etc/monit/conf.d/php-$meta[_version]-fpm.conf";
        $apache_conf_file = "/etc/apache2/php/php-$meta[_version]-fpm.conf";

        if(empty($meta['fpm']['port']) || (isset($meta['installed']) && !$meta['installed'])){
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

        $monit_conf_template = Util::renderTemplate('monit/conf.d/php-fpm.conf.template', [
            'version' => $meta['_version'],
            'port'    => $meta['fpm']['port'],
        ], true);

        if(!file_exists($monit_conf_file) || md5($monit_conf_template) !== md5_file($monit_conf_file)){
            $this->output->writeInfo("Writing monit php-fpm config file '$monit_conf_file'");
            $fs->write($monit_conf_file, $monit_conf_template, 'root');
        }

        $apache_conf_template = Util::renderTemplate('apache/php/php-fpm.conf.template', [
            'version' => $meta['_version'],
            'port'    => $meta['fpm']['port'],
        ], true);

        if(!file_exists($apache_conf_file) || md5($apache_conf_template) !== md5_file($apache_conf_file)){
            $this->output->writeInfo("Writing apache php-fpm config file '$apache_conf_file'");
            $fs->write($apache_conf_file, $apache_conf_template, 'root');
        }
    }

    /**
     * Sets default php version
     *
     * @param $version
     * @throws \Exception
     */
    protected function setDefaultPhp($version) {

        $proc = $this->runScript('php.current');
        if(!$proc->isSuccessful()){
            $this->output->writeError("Failed to get current php version");
            return;
        }

        $current = null;
        $output = $proc->getOutput();
        if(preg_match('/php\-([0-9\.]*)/', $output, $matches)){
            $current = $matches[1];
        }

        if($current === $version){
            return;
        }

        // Use system php
        if(null === $version){
            $proc = $this->runScript('php.switchoff');
            if(!$proc->isSuccessful()){
                $this->output->writeError("Unable to switch off phpbrew");
            }

            return;
        }

        $proc = $this->runScript('php.switch', null, $version);
        if(!$proc->isSuccessful()){
            $this->output->writeError("Unable to switch to php $version");
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
        $force_stdout = null;
        if($args_size > 1){
            $force_stdout = array_shift($args);
        }

        $cmd = "/vagrant/provision/main/scripts/phpbrew/$script.sh";
        if(count($args) > 0){
            $cmd .= " " . implode(" ", $args);
        }

        $proc = Util::Process("sudo -u vagrant bash $cmd", $this->output, false, $force_stdout, null);

        return $proc;
    }
}
