<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Config;

/**
 * End Task Class.
 *
 * Excuted at the end of the provisioning process.
 *
 * @package Dashbrew\Cli\Tasks
 */
class EndTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The End task can only be run by the Provision command.");
        }

        // Write current config file to be used in the next provisioning process
        $this->output->writeDebug("Writing a copy of the current config.yaml file");
        Config::writeOld();
    }
}
