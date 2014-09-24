<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Task\Task;
use Dashbrew\Util\Util;
use Dashbrew\Util\Config;
use Dashbrew\Util\Process;

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

    protected function managePhp($meta) {

        $fs = Util::getFilesystem();
        $this->output->writeInfo("Checking php $meta[_version]");

        if(isset($meta['installed']) && !$meta['installed']){
            if(!$meta['_is_installed']){
                return;
            }

            $this->output->writeInfo("Removing php");
            if($this->runScript('phpbrew.php.remove.sh', true, $meta['_version'])){
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
        if($this->runScript('phpbrew.php.install.sh', true, $meta['_version'], $meta['variants'])){
            $this->output->writeInfo("Successfully built php");
        }
        else {
            $fs->remove($meta['_path']);
            throw new \Exception("Unable to build php");
        }
    }

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
                if($this->runScript('phpbrew.ext.install.sh', true, $meta['_version'], $extname)){
                    $this->output->writeInfo("Successfully installed $extname extension");
                }
                else {
                    throw new \Exception("Unable to install $extname extension");
                }
            }

            $ext_enabled = file_exists($ini);
            if($extmeta['enabled'] && !$ext_enabled){
                $this->output->writeInfo("Enabling $extname extension");
                if($this->runScript('phpbrew.ext.enable.sh', false, $meta['_version'], $extname)){
                    $this->output->writeInfo("Successfully enabled $extname extension");
                }
                else {
                    throw new \Exception("Unable to enable $extname extension");
                }
            }

            if (!$extmeta['enabled'] && $ext_enabled){
                $this->output->writeInfo("Disabling $extname extension");
                if($this->runScript('phpbrew.ext.disable.sh', false, $meta['_version'], $extname)){
                    $this->output->writeInfo("Successfully disabled $extname extension");
                }
                else {
                    throw new \Exception("Unable to disable $extname extension");
                }
            }
        }
    }

    protected function manageFpm($meta) {

        if(empty($meta['fpm']['port']) && empty($meta['_old']['fpm']['port']) ){
            return;
        }

        $this->output->writeInfo("Checking fpm");

        $fs = Util::getFilesystem();
        $monit_conf_file = "/etc/monit/conf.d/php-$meta[_version]-fpm.conf";
        $apache_conf_file = "/etc/apache2/php/php-$meta[_version]-fpm.conf";

        if(empty($meta['fpm']['port']) || (isset($meta['installed']) && !$meta['installed'])){
            $fs->remove($monit_conf_file);
            $fs->remove($apache_conf_file);
            return;
        }

        $fpm_config_file = $meta['_path'] . '/etc/php-fpm.conf';
        $fpm_config_updated_1 = Util::augeas('PHP', $fpm_config_file, 'www/listen', '127.0.0.1:' . $meta['fpm']['port']);
        $fpm_config_updated_2 = Util::augeas('PHP', $fpm_config_file, 'www/user', 'www-data');
        $fpm_config_updated_3 = Util::augeas('PHP', $fpm_config_file, 'www/group', 'www-data');
        if($fpm_config_updated_1 || $fpm_config_updated_2 || $fpm_config_updated_3){
            $this->output->writeInfo("Configured fpm");
        }

        $monit_conf_template = Util::renderTemplate('monit/conf.d/php-fpm.conf.template', [
            'version' => $meta['_version'],
            'port'    => $meta['fpm']['port'],
        ], true);

        if(!file_exists($monit_conf_file) || md5($monit_conf_template) !== md5_file($monit_conf_file)){
            $this->output->writeInfo("Writing $monit_conf_file");
            if(!file_put_contents($monit_conf_file, $monit_conf_template)){
                throw new \Exception("Failed writing monit php-fpm config file $monit_conf_file");
            }

            $fs->chown($monit_conf_file, 'root');
            $fs->chgrp($monit_conf_file, 'root');
        }

        $apache_conf_template = Util::renderTemplate('apache/php/php-fpm.conf.template', [
            'version' => $meta['_version'],
            'port'    => $meta['fpm']['port'],
        ], true);

        if(!file_exists($apache_conf_file) || md5($apache_conf_template) !== md5_file($apache_conf_file)){
            $this->output->writeInfo("Writing $apache_conf_file");
            if(!file_put_contents($apache_conf_file, $apache_conf_template)){
                throw new \Exception("Failed writing apache php-fpm config file $apache_conf_file");
            }

            $fs->chown($apache_conf_file, 'root');
            $fs->chgrp($apache_conf_file, 'root');
        }
    }

    protected function setDefaultPhp($version) {

        // Use system php
        if(null === $version){
            if(!$this->runScript('phpbrew.php.switch_sys.sh', false)){
                throw new \Exception("Unable to switch to system php");
            }
            return;
        }

        if(!$this->runScript('phpbrew.php.switch.sh', false, $version)){
            throw new \Exception("Unable to switch to php $version");
        }
    }

    protected function runScript() {

        $args = func_get_args();
        $args_size = func_num_args();
        if($args_size < 2){
            throw new \Exception("runScript() function requires at lease two arguments, $args_size was given");
        }

        $script = array_shift($args);
        $send_output = array_shift($args);

        $script_path = "/vagrant/provision/main/scripts/$script";
        $script_args = implode(" ", $args);

        $success = Util::Process("sudo -u vagrant bash $script_path $script_args", $this->output, $send_output, null);
        if ($success) {
            return true;
        }

        return false;
    }
}
