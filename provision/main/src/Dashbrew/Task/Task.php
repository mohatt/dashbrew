<?php

namespace Dashbrew\Task;

use Dashbrew\Command\Command;
use Dashbrew\Input\Input;
use Dashbrew\Input\InputInterface;
use Dashbrew\Output\Output;
use Dashbrew\Output\OutputInterface;

/**
 * Task Class.
 *
 * @package Dashbrew\Task
 */
class Task implements TaskInterface {

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
     * @param Command $cmd
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \LogicException
     */
    public function init(Command $cmd, InputInterface $input, OutputInterface $output){

        $this->command  = $cmd;
        $this->input    = $input;
        $this->output   = $output;
    }

    /**
     * @throws \LogicException
     */
    public function run(){
        throw new \LogicException('You must override the execute() method in the concrete command class.');
    }
}
