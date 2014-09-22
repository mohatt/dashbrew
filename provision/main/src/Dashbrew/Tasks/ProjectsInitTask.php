<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Task\Task;
use Dashbrew\Util\Util;
use Dashbrew\Util\Registry;
use Dashbrew\Util\Finder;

/**
 * ProjectsInit Task Class.
 *
 * @package Dashbrew\Tasks
 */
class ProjectsInitTask extends Task {

    const PROJECTS_HOSTS_FILE    = '/vagrant/provision/main/etc/hosts.json';
    const PROJECTS_CATALOG_FILE  = '/vagrant/provision/main/etc/projects.json';

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

        $this->output->writeInfo("Finding projects");

        $projects_catalog = [];
        if(file_exists(self::PROJECTS_CATALOG_FILE)){
            $projects_catalog = json_decode(file_get_contents(self::PROJECTS_CATALOG_FILE), true);
        }

        $hosts = [];
        $projects = [
            'leave'   => [],
            'modify'  => [],
            'create'  => [],
            'delete'  => [],
        ];

        $yaml = Util::getYamlParser();

        $finder = new Finder;
        $finder->files()
            ->in('/vagrant/public')
            ->name('Projectfile.yaml')
            ->depth('< 5')
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b){
                return strcmp($a->getPath(), $b->getPath());
            });

        foreach($finder as $file){
            $file_path = $file->getPathname();

            try {
                $file_projects = $yaml->parse(file_get_contents($file_path));
            }
            catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
                $this->output->writeError("Failed parsing '$file_path': " . $e->getMessage());
                continue;
            }

            foreach($file_projects as $id => $project) {
                # Add project file path
                $project['_path'] = $file_path;

                if(isset($projects_catalog[$id])){
                    if($project == $projects_catalog[$id]){
                        $projects['leave'][$id] = $project;
                    }
                    else {
                        $projects['modify'][$id] = $project;
                    }
                }
                else {
                    # Ignore duplicates
                    if (isset($projects['create'][$id])){
                        $this->output->writeError(
                            "Unable to proccess project '$id' at '$project[_path]', " .
                            "another project with the same name is already exist at '$projects[create][$id][_path]'."
                        );

                        continue;
                    }

                    $projects['create'][$id] = $project;
                }

                if(isset($projects_catalog[$id])){
                    unset($projects_catalog[$id]);
                }

                if(!empty($project['vhost']['servername'])){
                    $hosts[] = $project['vhost']['servername'];
                }
                else {
                    $hosts[] = $id;
                }

                if(isset($project['vhost']['serveraliases'])){
                    foreach($project['vhost']['serveraliases'] as $serveralias){
                        $hosts[] = $serveralias;
                    }
                }
            }
        }

        # Prevent project duplicates
        foreach($projects['create'] as $id => $project){
            foreach(['leave', 'modify'] as $action){
                if (isset($projects[$action][$id])){
                    $this->output->writeError(
                        "Unable to proccess project '$id' at '$project[_path]', " .
                        "another project with the same name is already exist at '$projects[$action][$id][_path]'."
                    );

                    unset($projects['create'][$id]);
                }
            }
        }

        # Projects that are no longer exist needs to be deleted
        foreach($projects_catalog as $id => $project){
            $projects['delete'][$id] = $project;
        }

        Registry::set('projects', $projects);

        if(count($projects['modify']) == 0 && count($projects['create']) == 0 && count($projects['delete']) == 0){
            return;
        }

        $this->output->writeInfo(
            "Found " . (
                count($projects['leave']) +
                count($projects['modify']) +
                count($projects['create']) +
                count($projects['delete'])
            ) . " project(s)" .
            (count($projects['leave'])  > 0 ? "\n        -> " . (count($projects['leave'])) .  " don't have changes" : "") .
            (count($projects['create']) > 0 ? "\n        -> " . (count($projects['create'])) . " to be created" : "") .
            (count($projects['modify']) > 0 ? "\n        -> " . (count($projects['modify'])) . " to be modified" : "") .
            (count($projects['delete']) > 0 ? "\n        -> " . (count($projects['delete'])) . " to be deleted" : "")
        );

        # Write hosts file so that it can be accessed later by the Hostmanager plugin
        $this->output->writeInfo("Writing '" . self::PROJECTS_HOSTS_FILE . "'");
        if(!file_put_contents(self::PROJECTS_HOSTS_FILE, json_encode($hosts))){
            $this->output->writeError("Unable to write '" . self::PROJECTS_HOSTS_FILE . "'");
        }

        # Write projects catalog file
        $this->output->writeInfo("Writing '" . self::PROJECTS_CATALOG_FILE . "'");
        if(!file_put_contents(self::PROJECTS_CATALOG_FILE, json_encode(array_merge($projects['leave'], $projects['modify'], $projects['create'])))){
            $this->output->writeError("Unable to write '" . self::PROJECTS_CATALOG_FILE . "'");
        }
    }
}
