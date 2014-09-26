<?php

namespace Dashbrew\Cli\Task;

use Dashbrew\Cli\Command\Command;
use Dashbrew\Cli\Input\Input;
use Dashbrew\Cli\Input\InputInterface;
use Dashbrew\Cli\Output\Output;
use Dashbrew\Cli\Output\OutputInterface;

/**
 * Task Class.
 *
 * @package Dashbrew\Cli\Task
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
