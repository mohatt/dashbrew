<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;

/**
 * InitialSetup Task Class.
 *
 * @package Dashbrew\Cli\Tasks
 */
class InitialSetupTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The InitialSetup task can only be run by the Provision command.");
        }

        $lock = '/etc/dashbrew/initialsetup.lock';
        if(file_exists($lock)){
            return;
        }

        $fs = Util::getFilesystem();
        $fs->copy(
          '/vagrant/provision/main/config/config.yaml',
          '/vagrant/provision/main/etc/config.yaml.old',
          true,
          'vagrant'
        );

        $fs->mkdir(dirname($lock));
        $fs->touch($lock);
    }
}
