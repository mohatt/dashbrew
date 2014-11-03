<?php

namespace Dashbrew\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dashbrew\Cli\Task\Runner as TaskRunner;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Tasks\InitialSetupTask;
use Dashbrew\Cli\Tasks\InitTask;
use Dashbrew\Cli\Tasks\ConfigDefaultsTask;
use Dashbrew\Cli\Tasks\ConfigSyncTask;
use Dashbrew\Cli\Tasks\PackagesTask;
use Dashbrew\Cli\Tasks\PhpTask;
use Dashbrew\Cli\Tasks\ProjectsInitTask;
use Dashbrew\Cli\Tasks\ProjectsProcessTask;
use Dashbrew\Cli\Tasks\ProjectsEndTask;
use Dashbrew\Cli\Tasks\ServiceRestartTask;
use Dashbrew\Cli\Tasks\EndTask;

class ProvisionCommand extends Command {

    protected function configure() {

        $this->setName('provision');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $sw = Util::getStopwatch();
        $sw->start('provision');

        try {
            // Run provisioning tasks
            $this->runTasks($input, $output);
        } catch(\Exception $e){
            $output->writeError($e->getMessage());
        }

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
        $runner->addTask(new PackagesTask);
        $runner->addTask(new PhpTask);
        $runner->addTask(new ProjectsInitTask);
        $runner->addTask(new ProjectsProcessTask);
        $runner->addTask(new ProjectsEndTask);
        $runner->addTask(new ServiceRestartTask);
        $runner->addTask(new ConfigDefaultsTask);
        $runner->addTask(new ConfigSyncTask);
        $runner->addTask(new EndTask);

        $runner->run();
    }
}
