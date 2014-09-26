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
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

        $this->output->writeInfo("Checking packages");

        $this->manageOsPackages();
        $this->manageApacheModules();
        $this->manageNpmPackages();
    }

    /**
     * Manages os system packages via apt-get
     */
    protected function manageOsPackages(){

        $packages = [
            'install' => [],
            'remove'  => []
        ];

        $packages_config = Config::get('os::packages');
        foreach($packages_config as $package => $installed) {
            $is_installed = false;
            $proc = Util::process('dpkg-query -W -f=\'${Status}\' ' . $package, $this->output, true);
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
            Util::process("apt-get -y update", $this->output, false, null, null);
        }

        foreach($packages['remove'] as $package){
            $this->output->writeInfo("Uninstalling OS package '$package'");

            $proc_remove = Util::process("apt-get -y remove $package", $this->output);
            $proc_autoremove = Util::process("apt-get -y autoremove", $this->output);
            if(!$proc_remove->isSuccessful() || !$proc_autoremove->isSuccessful()){
                $this->output->writeError("Error occured while uninstalling OS package '$package'");
                continue;
            }

            $this->output->writeInfo("Successfully uninstalled OS package '$package'");
        }

        foreach($packages['install'] as $package){
            $this->output->writeInfo("Installing OS package '$package'");

            $proc = Util::process("apt-get -y install $package", $this->output, false, null, null);
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

        $modules = [
            'enable'   => [],
            'disable'  => []
        ];

        $modules_config = Config::get('apache::modules');
        foreach($modules_config as $module => $installed) {
            $is_installed = false;
            $proc = Util::process("a2query -m $module", $this->output, true);
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

            $proc = Util::process("a2dismod $modules_disable", $this->output);
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

            $proc = Util::process("a2enmod $modules_enable", $this->output);
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

        $packages = [
            'install' => [],
            'remove'  => []
        ];

        $packages_config = Config::get('npm::packages');
        foreach($packages_config as $package => $installed) {
            $is_installed = false;
            $proc = Util::process("npm -j ls -g $package", $this->output, true);
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

            $proc = Util::process("npm uninstall -g $package", $this->output);
            if(!$proc->isSuccessful()){
                $this->output->writeError("Error occured uninstalling npm package '$package'");
                continue;
            }

            $this->output->writeInfo("Successfully uninstalled npm package '$package'");
        }

        foreach($packages['install'] as $package){
            $this->output->writeInfo("Installing npm package '$package'");

            $proc = Util::process("npm install -g $package", $this->output, false, null, null);
            if(!$proc->isSuccessful()){
                $this->output->writeError("Error occured while installing npm package '$package'");
                continue;
            }

            $this->output->writeInfo("Successfully installed npm package '$package'");
        }
    }
}
