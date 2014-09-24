<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Output\Output;
use Dashbrew\Task\Task;
use Dashbrew\Util\Util;
use Dashbrew\Util\Config;
use Dashbrew\Util\Registry;
use Dashbrew\Util\Finder;

/**
 * Init Task Class.
 *
 * @package Dashbrew\Tasks
 */
class InitTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

        // Parse & initialize config.yaml file
        $this->output->writeInfo("Initializing config.yaml");
        Config::init();

        if(Config::get('debug')){
            $this->output->setVerbosity(Output::VERBOSITY_DEBUG);
        }

        // Initialize config files and directories
        $this->output->writeDebug("Initializing config files");

        $config_files = [
            [
                'path'    => '/etc/monit/monitrc',
                'source'  => '/vagrant/config/monit/monitrc',
                'default' => '/vagrant/provision/main/config/monit/monitrc',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/apache2/apache2.conf',
                'source'  => '/vagrant/config/apache2/apache2.conf',
                'default' => '/vagrant/provision/main/config/apache2/apache2.conf',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/mysql/my.cnf',
                'source'  => '/vagrant/config/mysql/my.cnf',
                'default' => '/etc/mysql/my.cnf',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/php5/cli/php.ini',
                'source'  => '/vagrant/config/php/cli/php.ini',
                'default' => '/etc/php5/cli/php.ini',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/opt/phpbrew/config.yaml',
                'source'  => '/vagrant/config/phpbrew/config.yaml',
                'default' => '/opt/phpbrew/config.yaml',
                'owner'   => 'vagrant',
                'group'   => 'vagrant',
            ],
            [
                'path'    => '/opt/pma/config.inc.php',
                'source'  => '/vagrant/config/pma/config.inc.php',
                'default' => '/vagrant/provision/main/config/pma/config.inc.php',
                'owner'   => 'www-data',
                'group'   => 'www-data',
            ],
        ];

        $config_dirs = [
            [
                'path'    => '/etc/monit/conf.d',
                'source'  => '/vagrant/config/monit/conf.d',
                'default' => '/vagrant/provision/main/config/monit/conf.d',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/php5/cli/conf.d',
                'source'  => '/vagrant/config/php/cli/conf.d',
                'default' => '/etc/php5/cli/conf.d',
                'owner'   => 'root',
                'group'   => 'root',
            ]
        ];

        $phps = Util::getInstalledPhps();
        foreach ($phps as $php_dirname) {

            $config_dirs[]  = [
                'path'    => "/opt/phpbrew/php/$php_dirname/var/db",
                'source'  => "/vagrant/config/phpbrew/$php_dirname/conf.d",
                'default' => "/opt/phpbrew/php/$php_dirname/var/db",
                'owner'   => 'vagrant',
                'group'   => 'vagrant',
            ];

            $config_files[] = [
                'path'    => "/opt/phpbrew/php/$php_dirname/etc/php.ini",
                'source'  => "/vagrant/config/phpbrew/$php_dirname/php.ini",
                'default' => "/opt/phpbrew/php/$php_dirname/etc/php.ini",
                'owner'   => 'vagrant',
                'group'   => 'vagrant',
            ];

            $config_files[] = [
                'path'    => "/opt/phpbrew/php/$php_dirname/etc/php-fpm.conf",
                'source'  => "/vagrant/config/phpbrew/$php_dirname/php-fpm.conf",
                'default' => "/opt/phpbrew/php/$php_dirname/etc/php-fpm.conf",
                'owner'   => 'vagrant',
                'group'   => 'vagrant',
            ];
        }

        Registry::set('config_files', $config_files);
        Registry::set('config_dirs', $config_dirs);
    }
}
