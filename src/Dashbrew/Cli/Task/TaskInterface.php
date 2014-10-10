<?php

namespace Dashbrew\Cli\Task;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Task Interface.
 *
 * @package Dashbrew\Cli\Task
 */
interface TaskInterface {


    /**
     * Initiates the task.
     *
     * @param Command $cmd
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function init(Command $cmd, InputInterface $input, OutputInterface $output);

    /**
     * Runs the task.
     */
    public function run();
}
