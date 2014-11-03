<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Registry;
use Dashbrew\Cli\Util\Projects;

/**
 * ProjectsEnd Task Class.
 *
 * @package Dashbrew\Cli\Tasks
 */
class ProjectsEndTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The ProjectsEnd task can only be run by the Provision command.");
        }

        $projects  = Registry::get('projects', []);
        $hosts     = Registry::get('projectsHosts', []);
        $shortcuts = Registry::get('projectsShortcuts', []);

        # Write projects catalog file
        $projects_catalog = array_merge($projects['skip'], $projects['check'], $projects['modify'], $projects['create']);
        if(Projects::writeCatalog($projects_catalog)){
            $this->output->writeInfo("Updated projects catalog file");
        }

        # Write hosts file so that it can be accessed later by the Hostmanager plugin
        if(Projects::writeHosts($hosts)){
            $this->output->writeInfo("Updated projects hosts file");
        }

        # Write shortcuts file so that it can be accessed later by dashbrew web dashboard
        if(Projects::writeShortcuts($shortcuts)){
            $this->output->writeInfo("Updated projects shortcuts file");
        }
    }
}
