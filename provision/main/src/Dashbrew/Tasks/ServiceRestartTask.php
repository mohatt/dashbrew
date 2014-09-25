<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Task\Task;
use Dashbrew\Util\Util;

/**
 * ServiceRestart Task Class.
 *
 * @package Dashbrew\Tasks
 */
class ServiceRestartTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

        $this->output->writeInfo("Restarting services");

        Util::process("monit quit", $this->output);

        // wait until monit quits
        while(file_exists('/var/run/monit.pid')) usleep(500000);

        Util::process("monit restart all", $this->output);
        Util::process("monit", $this->output);
    }
}
