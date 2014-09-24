<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Task\Task;
use Dashbrew\Util\Process;
use Dashbrew\Util\Util;
use Dashbrew\Util\Config;

/**
 * Packages Task Class.
 *
 * @package Dashbrew\Tasks
 */
class PackagesTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

        $this->manageOsPackages();

    }

    protected function manageOsPackages(){

        $packages = [
            'install' => [],
            'remove'  => []
        ];

        $packages_config = Config::get('os::packages');
        foreach($packages_config as $package => $installed) {
            $is_installed = false;
            if(0 == Util::exec('dpkg-query -W -f=\'${Status}\' ' . $package .' 2>/dev/null', true, $output)){
                $is_installed = (false !== stripos(implode(" ", $output), 'install ok installed'));
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
            Util::process("apt-get -y update", $this->output, false, null);
        }

        foreach($packages['remove'] as $package){
            $this->output->writeInfo("Uninstalling OS package '$package'");
            if(!Util::process("apt-get -y remove $package", $this->output)
                || !Util::process("apt-get -y autoremove", $this->output)){
                $this->output->writeError("Error occured uninstalling OS package");
            }
        }

        foreach($packages['install'] as $package){
            $this->output->writeInfo("Installing OS package '$package'");
            if(!Util::process("apt-get -y install $package", $this->output)){
                $this->output->writeError("Error occured installing OS package");
            }
        }
    }
}
