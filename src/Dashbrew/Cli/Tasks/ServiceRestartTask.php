<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\ServiceManager;

/**
 * ServiceRestart Task Class.
 *
 * Restarts system services
 *
 * @package Dashbrew\Cli\Tasks
 */
class ServiceRestartTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The ServiceRestart task can only be run by the Provision command.");
        }

        $this->output->writeInfo("Restarting services");

        $this->output->writeDebug("Stopping monit");
        Util::process($this->output, "monit quit");
        // wait until monit quits
        while(file_exists('/var/run/monit.pid')) usleep(500000);

        $services = ServiceManager::getServices();
        $this->output->writeDebug("Stopping services");
        foreach($services as $service){
            Util::process($this->output, "monit stop $service");
        }

        $this->output->writeDebug("Starting services");
        foreach($services as $service){
            Util::process($this->output, "monit start $service");
        }

        $this->output->writeDebug("Starting monit");
        Util::process($this->output, "monit");
    }
}
