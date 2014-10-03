<?php

namespace Dashbrew\Cli;

use Dashbrew\Cli\Input\Input;
use Dashbrew\Cli\Input\InputInterface;
use Dashbrew\Cli\Output\Output;
use Dashbrew\Cli\Output\OutputInterface;

/**
 * Application Class.
 *
 * @package Dashbrew
 */
class Application extends \Symfony\Component\Console\Application {

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands() {

        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new Commands\ProvisionCommand();

        return $defaultCommands;
    }

    /**
     * Runs the application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     * @return int 0 if everything went fine, or an error code
     */
    public function run(InputInterface $input = null, OutputInterface $output = null) {

        if (null === $input) {
            $input = new Input();
        }

        if (null === $output) {
            $output = new Output();
        }

        return parent::run($input, $output);
    }

    /**
     * Renders a caught exception.
     *
     * @param \Exception      $e      An exception instance
     * @param OutputInterface $output An OutputInterface instance
     */
    public function renderException($e, $output) {
        do {
            $output->writeln(sprintf('[%s] %s (%s:%s)',
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine())
            );
        } while ($e = $e->getPrevious());
    }
}
