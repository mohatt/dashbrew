<?php

namespace Dashbrew\Task;

use Dashbrew\Command\Command;
use Dashbrew\Input\Input;
use Dashbrew\Input\InputInterface;
use Dashbrew\Output\Output;
use Dashbrew\Output\OutputInterface;

/**
 * Task Runner Class.
 *
 * @package Dashbrew\Task
 */
class Runner {

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var Output
     */
    protected $output;

    /**
     * @var array
     */
    protected $tasks;

    /**
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(Command $command, InputInterface $input, OutputInterface $output) {

        $this->command  = $command;
        $this->input    = $input;
        $this->output   = $output;
        $this->tasks    = [];
    }

    /**
     * @param TaskInterface $task
     */
    public function addTask(TaskInterface $task){

        $this->tasks[] = $task;
    }

    /**
     * @param void
     */
    public function run(){

        foreach ($this->tasks as $task){
            $taskName = get_class($task);

            $this->output->writeDebug("Running task {$taskName}");

            $task->init($this->command, $this->input, $this->output);
            $task->run();

            $this->output->writeDebug("Finished running task {$taskName}");
        }
    }
}
