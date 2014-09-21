<?php

namespace Dashbrew\Task;

use Dashbrew\Command\Command;
use Dashbrew\Input\InputInterface;
use Dashbrew\Output\OutputInterface;

/**
 * Task Interface.
 *
 * @package Dashbrew\Task
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
