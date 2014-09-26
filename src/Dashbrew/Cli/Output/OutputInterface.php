<?php

namespace Dashbrew\Cli\Output;

/**
 * Output Interface.
 *
 * @package Dashbrew\Cli\Output
 */
interface OutputInterface extends \Symfony\Component\Console\Output\ConsoleOutputInterface {

    const PREFIX_INFO    = "Info";
    const PREFIX_DEBUG   = "Debug";
    const PREFIX_ERROR   = "Error";
    const PREFIX_STDOUT  = "Stdout";
    const PREFIX_STDERR  = "Stderr";

    /**
     * @param string $message
     */
    public function writeInfo($message);

    /**
     * @param string $message
     */
    public function writeDebug($message);

    /**
     * @param string $message
     */
    public function writeError($message);

}
