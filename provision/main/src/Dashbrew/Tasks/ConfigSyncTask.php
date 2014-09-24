<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Task\Task;
use Dashbrew\Util\Util;
use Dashbrew\Util\Registry;
use Dashbrew\Util\Finder;

/**
 * ConfigSync Task Class.
 *
 * @package Dashbrew\Tasks
 */
class ConfigSyncTask extends Task {

    const DIR_SYNC_STATUS_FILE = '/vagrant/provision/main/etc/config_dirs_status.json';

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

        $this->output->writeDebug("Checking config files");
        $this->syncFiles();

        $this->output->writeDebug("Checking config directories");
        $this->syncDirs();
    }

    protected function syncFiles() {

        $fs = Util::getFilesystem();
        $config_files = Registry::get('config_files');

        foreach ($config_files as $file){
            if(empty($file['path'])){
                continue;
            }

            $source_file = $file['source'];
            $target_file = $file['path'];

            if(file_exists($target_file)){
                if(md5_file($source_file) === md5_file($target_file)){
                    continue;
                }

                $target_file_time = filemtime($target_file);
            }
            else {
                $target_file_time = 0;
            }

            $origin_file_time = filemtime($source_file);
            if($origin_file_time >= $target_file_time){
                $copy_from  = $source_file;
                $copy_to    = $target_file;
                $copy_owner = !empty($file['owner']) ? $file['owner'] : null;
                $copy_group = !empty($file['group']) ? $file['group'] : null;
            }
            else {
                $copy_from  = $target_file;
                $copy_to    = $source_file;
                $copy_owner = 'vagrant';
                $copy_group = 'vagrant';
            }

            $this->output->writeInfo("Syncing config file '$source_file' " . ($copy_from == $source_file ? "->" : "<-" ) . " '$target_file'");
            $fs->copy($copy_from, $copy_to, true, $copy_owner, $copy_group);
        }
    }

    protected function syncDirs() {

        $fs = Util::getFilesystem();
        $sync_status = [];
        if(file_exists(self::DIR_SYNC_STATUS_FILE)){
            $sync_status = json_decode(file_get_contents(self::DIR_SYNC_STATUS_FILE), true);
        }
        $sync_status_new = [];
        $config_dirs = Registry::get('config_dirs');

        foreach ($config_dirs as $dir){
            if(empty($dir['path'])){
                continue;
            }

            $source_dir       = $dir['source'];
            $source_dir_owner = 'vagrant';
            $source_dir_group = 'vagrant';
            $target_dir       = $dir['path'];
            $target_dir_owner = !empty($dir['owner']) ? $dir['owner'] : null;
            $target_dir_group = !empty($dir['group']) ? $dir['group'] : null;

            $dir_sync_status = ['old' => [], 'new' => []];
            if(isset($sync_status[$target_dir])){
                $dir_sync_status['old'] = $sync_status[$target_dir];
            }

            $finder = new Finder;
            foreach($finder->files()->in($source_dir)->ignoreDotFiles(false)->depth('== 0') as $source_dir_file){
                $source_dir_filename = $source_dir_file->getFilename();

                $source_dir_filepath = $source_dir . '/' . $source_dir_filename;
                $target_dir_filepath = $target_dir . '/' . $source_dir_filename;

                // Sync file changes
                if(file_exists($target_dir_filepath)){
                    $dir_sync_status['new'][] = $source_dir_filename;

                    if(md5_file($source_dir_filepath) === md5_file($target_dir_filepath)){
                        continue;
                    }

                    $source_dir_filetime = filemtime($source_dir_filepath);
                    $target_dir_filetime = filemtime($target_dir_filepath);

                    if($source_dir_filetime >= $target_dir_filetime){
                        $copy_from  = $source_dir_filepath;
                        $copy_to    = $target_dir_filepath;
                        $copy_owner = $target_dir_owner;
                        $copy_group = $target_dir_group;
                    }
                    else {
                        $copy_from  = $target_dir_filepath;
                        $copy_to    = $source_dir_filepath;
                        $copy_owner = $source_dir_owner;
                        $copy_group = $source_dir_group;
                    }

                    $this->output->writeInfo("Syncing config file '$source_dir_filepath' " . ($copy_from == $source_dir_filepath ? "->" : "<-" ) . " '$target_dir_filepath'");
                    $fs->copy($copy_from, $copy_to, true, $copy_owner, $copy_group);
                }
                // Delete file from source dir
                else if(in_array($source_dir_filename, $dir_sync_status['old'])){
                    $this->output->writeInfo("Removing config file '$source_dir_filepath'");
                    $fs->remove($source_dir_filepath);
                }
                // Copy file to target dir
                else {
                    $dir_sync_status['new'][] = $source_dir_filename;

                    $this->output->writeInfo("Copying config file '$source_dir_filepath' -> '$target_dir_filepath'");
                    $fs->copy($source_dir_filepath, $target_dir_filepath, true, $target_dir_owner, $target_dir_group);
                }
            }

            $finder = new Finder;
            foreach($finder->files()->in($target_dir)->ignoreDotFiles(false)->depth('== 0') as $target_dir_file){
                $target_dir_filename = $target_dir_file->getFilename();

                $source_dir_filepath = $source_dir . '/' . $target_dir_filename;
                $target_dir_filepath = $target_dir . '/' . $target_dir_filename;

                if(in_array($target_dir_filename, $dir_sync_status['new'])){
                    continue;
                }

                // Delete file from target dir
                if(in_array($target_dir_filename, $dir_sync_status['old'])){
                    $this->output->writeInfo("Removing config file '$target_dir_filepath'");
                    $fs->remove($target_dir_filepath);
                }
                // Copy file to source dir
                else {
                    $this->output->writeInfo("Copying config file '$source_dir_filepath' <- '$target_dir_filepath'");
                    $fs->copy($target_dir_filepath, $source_dir_filepath, true, $source_dir_owner, $source_dir_group);

                    $dir_sync_status['new'][] = $target_dir_filename;
                }
            }

            $sync_status_new[$target_dir] = $dir_sync_status['new'];
        }

        // Write status file
        $this->output->writeDebug("Writing config directories sync status file");
        file_put_contents(self::DIR_SYNC_STATUS_FILE, json_encode($sync_status_new));
    }
}
