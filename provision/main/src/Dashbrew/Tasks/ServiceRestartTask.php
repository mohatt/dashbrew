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

        Util::exec("monit quit");

        // wait until monit quits
        while(file_exists('/var/run/monit.pid')) usleep(500000);

        Util::exec("monit restart all");
        Util::exec("monit");
    }
}
