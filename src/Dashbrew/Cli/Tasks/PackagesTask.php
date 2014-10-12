<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\Config;

/**
 * Packages Task Class.
 *
 * Manages different types of system packages.
 *
 * @package Dashbrew\Cli\Tasks
 */
class PackagesTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Packages task can only be run by the Provision command.");
        }

        $this->manageOsPackages();
        $this->manageApacheModules();
        $this->manageNpmPackages();
    }

    /**
     * Manages os system packages via apt-get
     */
    protected function manageOsPackages(){

        $packages_config = Config::get('os::packages');
        if(empty($packages_config)){
            return;
        }

        $this->output->writeInfo("Checking system packages");

        $packages = [
          'install' => [],
          'remove'  => []
        ];

        foreach($packages_config as $package => $installed) {
            $is_installed = false;
            $proc = Util::process($this->output, 'dpkg-query -W -f=\'${Status}\' ' . $package, ['stderr' => false]);
            if($proc->isSuccessful()){
                $is_installed = (false !== stripos($proc->getOutput(), 'install ok installed'));
            }

            if(($installed && $is_installed) || (!$installed && !$is_installed)){
                continue;
            }

            if(!$installed){
                $packages['remove'][] = $package;
                continue;
            }

            $packages['install'][] = $package;
        }

        if(count($packages['install']) > 0){
            $this->output->writeInfo("Running apt-get update");
            Util::process($this->output, "apt-get -y update", ['timeout' => null]);
        }

        foreach($packages['remove'] as $package){
            $this->output->writeInfo("Uninstalling OS package '$package'");

            $proc = Util::process($this->output, "apt-get -y remove $package");
            if(!$proc->isSuccessful()){
                $this->output->writeError("Error occured while uninstalling OS package '$package'");
                continue;
            }

            $this->output->writeInfo("Successfully uninstalled OS package '$package'");
        }

        if(count($packages['remove']) > 0){
            Util::process($this->output, "apt-get -y autoremove");
        }

        foreach($packages['install'] as $package){
            $this->output->writeInfo("Installing OS package '$package'");

            $proc = Util::process($this->output, "apt-get -y install $package", ['timeout' => null]);
            if(!$proc->isSuccessful()){
                $this->output->writeError("Error occured while installing OS package '$package'");
                continue;
            }

            $this->output->writeInfo("Successfully installed OS package '$package'");
        }
    }

    /**
     * Manages apache modules via a2enmod and a2dismod
     */
    protected function manageApacheModules(){

        $modules_config = Config::get('apache::modules');
        if(empty($modules_config)){
            return;
        }

        $this->output->writeInfo("Checking apache modules");

        $modules = [
            'enable'   => [],
            'disable'  => []
        ];

        foreach($modules_config as $module => $installed) {
            $is_installed = false;
            $proc = Util::process($this->output, "a2query -m $module", ['stderr' => false]);
            if($proc->isSuccessful()){
                $is_installed = (false !== stripos($proc->getOutput(), "$module (enabled"));
            }

            if(($installed && $is_installed) || (!$installed && !$is_installed)){
                continue;
            }

            if(!$installed){
                $modules['disable'][] = $module;
                continue;
            }

            $modules['enable'][] = $module;
        }

        if(count($modules['disable']) > 0){
            $modules_disable = implode(" ", $modules['disable']);
            $modules_disable_msg = implode("', '", $modules['disable']);

            $this->output->writeInfo("Disabling apache modules '$modules_disable_msg'");

            $proc = Util::process($this->output, "a2dismod $modules_disable");
            if(!$proc->isSuccessful()){
                $this->output->writeError("Error occured while disabling apache modules '$modules_disable_msg'");
            }
            else {
                $this->output->writeInfo("Successfully disabled apache modules '$modules_disable_msg'");
            }
        }

        if(count($modules['enable']) > 0){
            $modules_enable = implode(" ", $modules['enable']);
            $modules_enable_msg = implode("', '", $modules['enable']);

            $this->output->writeInfo("Enabling apache modules '$modules_enable_msg'");

            $proc = Util::process($this->output, "a2enmod $modules_enable");
            if(!$proc->isSuccessful()){
                $this->output->writeError("Error occured while enabling apache modules '$modules_enable_msg'");
            }
            else {
                $this->output->writeInfo("Successfully enabled apache modules '$modules_enable_msg'");
            }
        }
    }

    /**
     * Manages nodjs modules via npm
     */
    protected function manageNpmPackages(){

        $packages_config = Config::get('npm::packages');
        if(empty($packages_config)){
            return;
        }

        $this->output->writeInfo("Checking nodejs modules");

        $packages = [
            'install' => [],
            'remove'  => []
        ];

        foreach($packages_config as $package => $installed) {
            $is_installed = false;
            $proc = Util::process($this->output, "npm -j ls -g $package", ['stderr' => false]);
            if($proc->isSuccessful()){
                $is_installed = (false !== stripos($proc->getOutput(), '"' . $package . '": {'));
            }

            if(($installed && $is_installed) || (!$installed && !$is_installed)){
                continue;
            }

            if(!$installed){
                $packages['remove'][] = $package;
                continue;
            }

            $packages['install'][] = $package;
        }

        foreach($packages['remove'] as $package){
            $this->output->writeInfo("Uninstalling npm package '$package'");

            $proc = Util::process($this->output, "npm uninstall -g $package");
            if(!$proc->isSuccessful()){
                $this->output->writeError("Error occured uninstalling npm package '$package'");
                continue;
            }

            $this->output->writeInfo("Successfully uninstalled npm package '$package'");
        }

        foreach($packages['install'] as $package){
            $this->output->writeInfo("Installing npm package '$package'");

            $proc = Util::process($this->output, "npm install -g $package", ['timeout' => null]);
            if(!$proc->isSuccessful()){
                $this->output->writeError("Error occured while installing npm package '$package'");
                continue;
            }

            $this->output->writeInfo("Successfully installed npm package '$package'");
        }
    }
}
