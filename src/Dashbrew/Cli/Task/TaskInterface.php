<?php

namespace Dashbrew\Cli\Task;

use Dashbrew\Cli\Command\Command;
use Dashbrew\Cli\Input\InputInterface;
use Dashbrew\Cli\Output\OutputInterface;

/**
 * Task Interface.
 *
 * @package Dashbrew\Cli\Task
 */
interface TaskInterface {

    /**
     * Initiates the task.
     */
    public function init(Command $cmd, InputInterface $input, OutputInterface $output);

    /**
     * Runs the task.
     */
    public function run();
}
