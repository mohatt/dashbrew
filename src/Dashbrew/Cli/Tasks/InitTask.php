<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Output\Output;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\Config;
use Dashbrew\Cli\Util\SyncManager;
use Dashbrew\Cli\Util\ServiceManager;

/**
 * Init Task Class.
 *
 * Initializes configurations for config files and directories.
 *
 * @package Dashbrew\Cli\Tasks
 */
class InitTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Init task can only be run by the Provision command.");
        }

        // Check box version
        if(!file_exists('/etc/dashbrew/.version')){
            throw new \Exception("Unable to find base box version file.");
        }

        $box_version = file_get_contents('/etc/dashbrew/.version');
        if(empty($box_version)){
            throw new \Exception("Invalid base box version file.");
        }

        if(false === strpos($box_version, DASHBREW_BASEBOX_VERSION, 0)){
            throw new \Exception("Incompatible base box version ($box_version). Dashbrew requires a base box version ".DASHBREW_BASEBOX_VERSION.".x");
        }

        $this->output->writeDebug("Checking for available " . $box_version . " patches");
        $box_patch_file = '/etc/dashbrew/.patch';
        $box_patch_md5 = null;
        if(file_exists($box_patch_file)){
            $box_patch_md5 = file_get_contents($box_patch_file);
        }

        $available_patch = file_get_contents('https://raw.githubusercontent.com/mdkholy/dashbrew-basebox/master/patches/' . $box_version.  '.sh');
        $apply_patch = false;
        if(!empty($available_patch)){
            $available_patch_md5 = md5($available_patch);
            if($box_patch_md5 != $available_patch_md5){
                $apply_patch = true;
            }
        }

        if($apply_patch){
            $this->output->writeInfo("An update patch is available for your box. Updating...");
            $fs = Util::getFilesystem();

            $exec_patch_file = '/tmp/dashbrew-basebox-patch.sh';
            $fs->write($exec_patch_file, $available_patch, 'root');
            $fs->chmod($exec_patch_file, 0755);

            $proc = Util::Process($this->output, "sudo bash $exec_patch_file", ['timeout' => null]);
            if($proc->isSuccessful()){
                $this->output->writeInfo("Update patch has been applied successfully");
            }
            else {
                $this->output->writeError("Error occured while applying update patch");
            }

            //$fs->remove($exec_patch_file);

            $fs->write($box_patch_file, $available_patch_md5);
            $fs->chmod($box_patch_file, 0644);
        }

        //print_r($available_patch_md5);
        die;

        // Parse & initialize config.yaml file
        if(file_exists(Config::CONFIG_FILE)){
            $this->output->writeInfo("Initializing environment.yaml");
        }
        else {
            $this->output->writeInfo("Provisioning without environment.yaml config file");
        }
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
                'default' => '/etc/monit/monitrc',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/apache2/apache2.conf',
                'source'  => '/vagrant/config/apache/apache.conf',
                'default' => '/etc/apache2/apache2.conf',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/php5/fpm/php-fpm.conf',
                'source'  => '/vagrant/config/php/fpm/php-fpm.conf',
                'default' => '/etc/php5/fpm/php-fpm.conf',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/php5/fpm/php.ini',
                'source'  => '/vagrant/config/php/fpm/php.ini',
                'default' => '/etc/php5/fpm/php.ini',
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
                'path'    => '/usr/share/phpmyadmin/config.inc.php',
                'source'  => '/vagrant/config/phpmyadmin/config.inc.php',
                'default' => '/usr/share/phpmyadmin/config.inc.php',
                'owner'   => 'vagrant',
                'group'   => 'www-data',
            ],
        ];

        $config_dirs = [
            [
                'path'    => '/home/vagrant',
                'source'  => '/vagrant/config/home',
                'default' => '/home/vagrant',
                'owner'   => 'vagrant',
                'group'   => 'vagrant',
            ],
            [
                'path'    => '/etc/monit/conf.d',
                'source'  => '/vagrant/config/monit/conf.d',
                'default' => '/etc/monit/conf.d',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/php5/cli/conf.d',
                'source'  => '/vagrant/config/php/cli/conf.d',
                'default' => '/etc/php5/cli/conf.d',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/php5/fpm/conf.d',
                'source'  => '/vagrant/config/php/fpm/conf.d',
                'default' => '/etc/php5/fpm/conf.d',
                'owner'   => 'root',
                'group'   => 'root',
            ],
            [
                'path'    => '/etc/php5/fpm/pool.d',
                'source'  => '/vagrant/config/php/fpm/pool.d',
                'default' => '/etc/php5/fpm/pool.d',
                'owner'   => 'root',
                'group'   => 'root',
            ]
        ];

        $installed_phps = Util::getInstalledPhps();
        $phps = Config::get('php::builds');
        foreach ($phps as $build => $meta) {

            if(!in_array($build, $installed_phps)){
                continue;
            }

            $config_dirs[]  = [
                'path'    => "/opt/phpbrew/php/$build/var/db",
                'source'  => "/vagrant/config/phpbrew/$build/conf.d",
                'default' => "/opt/phpbrew/php/$build/var/db",
                'owner'   => 'vagrant',
                'group'   => 'vagrant',
            ];

            $config_files[] = [
                'path'    => "/opt/phpbrew/php/$build/etc/php.ini",
                'source'  => "/vagrant/config/phpbrew/$build/php.ini",
                'default' => "/opt/phpbrew/php/$build/etc/php.ini",
                'owner'   => 'vagrant',
                'group'   => 'vagrant',
            ];

            $config_files[] = [
                'path'    => "/opt/phpbrew/php/$build/etc/php-fpm.conf",
                'source'  => "/vagrant/config/phpbrew/$build/php-fpm.conf",
                'default' => "/opt/phpbrew/php/$build/etc/php-fpm.conf",
                'owner'   => 'vagrant',
                'group'   => 'vagrant',
            ];
        }

        foreach($config_files as $config_file){
            SyncManager::addRule(SyncManager::SYNC_FILE, $config_file);
        }

        foreach($config_dirs as $config_dir){
            SyncManager::addRule(SyncManager::SYNC_DIR, $config_dir);
        }

        $services = [
          'apache',
          'mysql',
          'php-system-fpm',
          'mailcatcher',
        ];

        foreach($services as $service){
            ServiceManager::addService($service);
        }
    }
}
