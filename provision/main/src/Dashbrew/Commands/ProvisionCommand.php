<?php

namespace Dashbrew\Commands;

use Dashbrew\Command\Command;
use Dashbrew\Input\InputInterface;
use Dashbrew\Output\OutputInterface;
use Dashbrew\Task\Runner as TaskRunner;
use Dashbrew\Tasks\InitialSetupTask;
use Dashbrew\Tasks\InitTask;
use Dashbrew\Tasks\ConfigDefaultsTask;
use Dashbrew\Tasks\ConfigSyncTask;
use Dashbrew\Tasks\PhpTask;
use Dashbrew\Tasks\ProjectsInitTask;
use Dashbrew\Tasks\ProjectsProcessTask;
use Dashbrew\Tasks\ServiceRestartTask;
use Dashbrew\Util\Config;
use Dashbrew\Util\Util;

class ProvisionCommand extends Command {

    protected function configure() {

        $this->setName('provision');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $sw = Util::getStopwatch();
        $sw->start('provision');

        // Run provisioning tasks
        $this->runTasks($input, $output);

        // Write current config file to be used in the next provisioning process
        Config::writeTemp();

        $duration = round($sw->stop('provision')->getDuration() / 1000, 2);
        $duration_unit = 's';
        if($duration > 60){
            $duration = round($duration / 60, 2);
            $duration_unit = 'm';
        }

        $output->writeInfo("Finished in {$duration}{$duration_unit}");
    }

    protected function runTasks(InputInterface $input, OutputInterface $output) {

        $output->writeDebug("Starting Task Runner...");

        $runner = new TaskRunner($this, $input, $output);

        $runner->addTask(new InitialSetupTask);
        $runner->addTask(new InitTask);
        $runner->addTask(new ConfigDefaultsTask);
        $runner->addTask(new ConfigSyncTask);
        $runner->addTask(new PhpTask);
        $runner->addTask(new ProjectsInitTask);
        $runner->addTask(new ProjectsProcessTask);
        $runner->addTask(new ServiceRestartTask);
        $runner->addTask(new ConfigSyncTask);

        $runner->run();
    }
}
