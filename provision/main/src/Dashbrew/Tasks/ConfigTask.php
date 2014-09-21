<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Task\Task;
use Dashbrew\Util\Util;
use Dashbrew\Util\Finder;

/**
 * UploadConfig Task Class.
 *
 * @package Dashbrew\Tasks
 */
class ConfigTask extends Task {

    /**
     * @var array
     */
    protected $config_files;

    /**
     * @var array
     */
    protected $config_dirs;

    /**
     * @var array
     */
    protected $config_dir_struct;

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

        // Initialize config files and directories
        $this->initConfigFiles();

        // Copy default config files for non-existing files
        $this->copyDefaultConfig();

        // Sync config files and folders between the host and the geust
        $this->syncConfigFiles();
    }

    public function initConfigFiles() {

        $this->output->writeInfo("Initializing config files");

        $this->config_files = [
            [
                'path'    => '/etc/monit/monitrc',
                'source'  => '/vagrant/provision/main/config/monit/monitrc',
                'default' => '',
            ],
            [
                'path'    => '/etc/apache2/apache2.conf',
                'source'  => '/vagrant/config/apache2/apache2.conf',
                'default' => '/vagrant/provision/main/config/apache2/apache2.conf',
            ],
            [
                'path'    => '/etc/mysql/my.cnf',
                'source'  => '/vagrant/config/mysql/my.cnf',
                'default' => '/vagrant/provision/main/config/mysql/my.cnf',
            ],
            [
                'path'    => '/etc/php5/cli/php.ini',
                'source'  => '/vagrant/config/php/cli/php.ini',
                'default' => '/vagrant/provision/main/config/php/cli/php.ini',
            ],
            [
                'path'    => '/opt/phpbrew/config.yaml',
                'source'  => '/vagrant/config/phpbrew/variants.yaml',
                'default' => '/vagrant/provision/main/config/phpbrew/variants.yaml',
            ],
            [
                'path'    => '/opt/pma/config.inc.php',
                'source'  => '/vagrant/config/pma/config.inc.php',
                'default' => '/vagrant/provision/main/config/pma/config.inc.php',
            ],
        ];

        $this->config_dirs = [
            [
                'path'   => '/etc/monit/conf.d',
                'source' => '/vagrant/provision/main/config/monit/conf.d',
                'owner'  => 'root',
                'group'  => 'root',
                'prefix' => '000-'
            ],
            [
                'path'   => '/etc/php5/cli/conf.d',
                'source' => '/vagrant/config/php/cli/conf.d',
                'owner'  => 'root',
                'group'  => 'root',
                'prefix' => 'zzz-'
            ]
        ];

        $this->config_dir_struct = [
            '/vagrant/config',
            '/vagrant/config/apache2',
            '/vagrant/config/mysql',
            '/vagrant/config/php',
            '/vagrant/config/php/cli',
            '/vagrant/config/php/cli/conf.d',
            '/vagrant/config/phpbrew',
            '/vagrant/config/pma',
        ];

        $phps = Util::getInstalledPhps();
        foreach ($phps as $php_dirname) {
            $this->config_dir_struct[]  = "/vagrant/config/phpbrew/$php_dirname";
            $this->config_dir_struct[]  = "/vagrant/config/phpbrew/$php_dirname/conf.d";

            $this->config_dirs[]  = [
                'path'   => "/opt/phpbrew/php/$php_dirname/var/db",
                'source' => "/vagrant/config/phpbrew/$php_dirname/conf.d",
                'owner'  => 'vagrant',
                'group'  => 'vagrant',
                'prefix' => 'zzz-'
            ];

            $this->config_files[] = [
                'path'    => "/opt/phpbrew/php/$php_dirname/etc/php.ini",
                'source'  => "/vagrant/config/phpbrew/$php_dirname/php.ini",
                'default' => "/opt/phpbrew/php/$php_dirname/etc/php.ini",
            ];

            $this->config_files[] = [
                'path'    => "/opt/phpbrew/php/$php_dirname/etc/php-fpm.conf",
                'source'  => "/vagrant/config/phpbrew/$php_dirname/php-fpm.conf",
                'default' => "/opt/phpbrew/php/$php_dirname/etc/php-fpm.conf",
            ];
        }
    }

    public function copyDefaultConfig() {

        $fs = Util::getFilesystem();
        if($fs->exists(array_column($this->config_files, 'source'))) {
            return;
        }

        $fs->mkdir($this->config_dir_struct, 0777, $fs::OWNER_VAGRANT);

        foreach ($this->config_files as $file){
            if(empty($file['default'])){
                continue;
            }

            $target_file = $file['source'];
            $origin_file = $file['default'];
            if(!file_exists($target_file)){
                $this->output->writeInfo("Copying default config file '$target_file'");
                $fs->copy($origin_file, $target_file, true, $fs::OWNER_VAGRANT);
            }
        }
    }

    public function syncConfigFiles() {

        $this->output->writeInfo("Checking config files");

        $fs = Util::getFilesystem();
        foreach ($this->config_files as $file){
            $target_file = $file['path'];
            $origin_file = $file['source'];

            if(!file_exists($target_file) ||  md5_file($origin_file) != md5_file($target_file)){
                $this->output->writeInfo("Syncing config file changes '$origin_file'");
                $fs->copy($origin_file, $target_file, true, null, null);
            }
        }

        foreach ($this->config_dirs as $dir){
            $target_dir = $dir['path'];
            $origin_dir = $dir['source'];
            $target_dir_owner = $dir['owner'];
            $target_dir_group = $dir['group'];
            $filename_prefix = '';
            if(isset($dir['prefix'])){
                $filename_prefix = $dir['prefix'];
            }

            $finder = new Finder;
            foreach($finder->files()->in($origin_dir)->depth('== 0') as $dir_file){
                $dir_filename = $dir_file->getFilename();
                $origin_dir_file = $origin_dir . '/' . $dir_filename;
                $target_dir_file = $target_dir . '/' . $filename_prefix . $dir_filename;

                if(!file_exists($target_dir_file) ||  md5_file($origin_dir_file) != md5_file($target_dir_file)){
                    $this->output->writeInfo("Syncing config file changes '$origin_dir_file'");
                    $fs->copy($origin_dir_file, $target_dir_file, true, $target_dir_owner, $target_dir_group);
                }
            }

            $finder = new Finder;
            foreach($finder->files()->in($target_dir)->depth('== 0') as $dir_file){
                $dir_filename = $dir_file->getFilename();
                if(!empty($filename_prefix) && false === strpos($dir_filename, $filename_prefix, 0)){
                    continue;
                }

                $origin_dir_file = $origin_dir . '/' . substr($dir_filename, strlen($filename_prefix));
                $target_dir_file = $target_dir . '/' . $dir_filename;

                if(!file_exists($origin_dir_file)){
                    $fs->remove($target_dir_file);
                }
            }
        }
    }
}
