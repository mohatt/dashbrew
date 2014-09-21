<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Task\Task;
use Dashbrew\Util\Util;
use Dashbrew\Util\Registry;

/**
 * ProjectsProcess Task Class.
 *
 * @package Dashbrew\Tasks
 */
class ProjectsProcessTask extends Task {
    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

    }

} 