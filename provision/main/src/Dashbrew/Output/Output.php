<?php

namespace Dashbrew\Output;

use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Output Class.
 *
 * @package Dashbrew\Output
 */
class Output extends ConsoleOutput implements OutputInterface {

    /**
     * {@inheritdoc}
     */
    public function writeInfo($message) {

        return $this->writeWithPrefix($this, $message, self::PREFIX_INFO);
    }

    /**
     * {@inheritdoc}
     */
    public function writeDebug($message) {

        if(!$this->isDebug()){
            return;
        }

        return $this->writeWithPrefix($this, $message, self::PREFIX_DEBUG);
    }

    /**
     * {@inheritdoc}
     */
    public function writeError($message) {

        return $this->writeWithPrefix($this->getErrorOutput(), $message, self::PREFIX_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    protected function writeWithPrefix($output, $message, $prefix) {

        return $output->writeln("[$prefix] $message", self::OUTPUT_NORMAL);
    }
}
