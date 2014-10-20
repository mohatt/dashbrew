<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\SyncManager;
use Dashbrew\Cli\Util\Finder;

/**
 * ConfigDefaults Task Class
 *
 * Copies default config files for non-existing files.
 *
 * @package Dashbrew\Cli\Tasks
 */
class ConfigDefaultsTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The ConfigDefaults task can only be run by the Provision command.");
        }

        $fs = Util::getFilesystem();

        $config_files = SyncManager::getRules(SyncManager::SYNC_FILE);;
        $config_dirs = SyncManager::getRules(SyncManager::SYNC_DIR);
        $config_sources = array_merge(
            array_column($config_files, 'source'),
            array_column($config_dirs, 'source')
        );

        if($fs->exists($config_sources)) {
            return;
        }

        $config_sources_dirs = [];
        foreach($config_sources as $config_source) {
            $config_source_dir = dirname($config_source);
            while($config_source_dir != '/vagrant' && !in_array($config_source_dir, $config_sources_dirs)) {
                $config_sources_dirs[] = $config_source_dir;
                $config_source_dir = dirname($config_source_dir);
            };
        }

        $fs->mkdir($config_sources_dirs, 0777, 'vagrant');

        foreach ($config_files as $file){
            if(empty($file['default'])){
                continue;
            }

            $target_file = $file['source'];
            $origin_file = $file['default'];
            if(!file_exists($target_file)){
                $this->output->writeInfo("Writing default config file '$target_file'");
                $fs->copy($origin_file, $target_file, true, 'vagrant');
            }
        }

        foreach ($config_dirs as $dir){
            if(empty($dir['default'])){
                continue;
            }

            $source_dir = $dir['default'];
            $target_dir = $dir['source'];
            if(file_exists($target_dir)){
                continue;
            }

            $this->output->writeInfo("Writing default config dir '$target_dir'");
            $fs->mkdir($target_dir, 0777, 'vagrant');
            $finder = new Finder;
            foreach($finder->files()->in($source_dir)->ignoreDotFiles(false)->depth('== 0') as $origin_dir_file){
                $origin_dir_filename = $origin_dir_file->getFilename();

                $target_dir_filepath = $target_dir . '/' . $origin_dir_filename;
                $origin_dir_filepath = $source_dir . '/' . $origin_dir_filename;

                $fs->copy($origin_dir_filepath, $target_dir_filepath, true, 'vagrant');
            }
        }
    }
}
