<?php

namespace Dashbrew\Cli\Output;

use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Output Class.
 *
 * @package Dashbrew\Cli\Output
 */
class Output extends ConsoleOutput {

    const PREFIX_INFO    = "Info";
    const PREFIX_DEBUG   = "Debug";
    const PREFIX_ERROR   = "Error";

    /**
     * {@inheritdoc}
     */
    public function writeInfo($message) {

        return $this->writeWithPrefix($this, $message, self::PREFIX_INFO, true);
    }

    /**
     * {@inheritdoc}
     */
    public function writeDebug($message) {

        if(!$this->isDebug()){
            return;
        }

        return $this->writeWithPrefix($this, $message, self::PREFIX_DEBUG, true);
    }

    /**
     * {@inheritdoc}
     */
    public function writeError($message) {

        return $this->writeWithPrefix($this->getErrorOutput(), $message, self::PREFIX_ERROR, true);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStdout($message) {

        return $this->write($message, false);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStderr($message) {

        return $this->getErrorOutput()->write($message, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function writeWithPrefix($output, $message, $prefix, $newline = false) {

        return $output->write("[$prefix] $message", $newline);
    }
}
