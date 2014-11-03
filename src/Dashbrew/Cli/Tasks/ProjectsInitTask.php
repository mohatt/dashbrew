<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\Config;
use Dashbrew\Cli\Util\Registry;
use Dashbrew\Cli\Util\Projects;
use Dashbrew\Cli\Util\Finder;

/**
 * ProjectsInit Task Class.
 *
 * Finds and initializes .dashbrew projects under public/ directory.
 *
 * @package Dashbrew\Cli\Tasks
 */
class ProjectsInitTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The ProjectsInit task can only be run by the Provision command.");
        }

        $this->output->writeInfo("Finding projects");

        $projects_catalog = Projects::get();
        $projects = [
            'skip'    => [],
            'check'   => [],
            'modify'  => [],
            'create'  => [],
            'delete'  => [],
        ];

        $yaml = Util::getYamlParser();

        $finder = new Finder;
        $finder->files()
            ->in('/vagrant/public')
            ->name('.dashbrew')
            ->ignoreDotFiles(false)
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
                $project['_path'] = $file_path;

                if(isset($projects_catalog[$id])){
                    $project['_created'] = $projects_catalog[$id]['_created'];
                    $project['_modified'] = $projects_catalog[$id]['_modified'];
                    if($project == $projects_catalog[$id]){
                        if($this->__projectNeedsCheck($id, $project)){
                            $projects['check'][$id] = $project;
                        }
                        else {
                            $projects['skip'][$id] = $project;
                        }
                    }
                    else {
                        $project['_modified'] = time();
                        $projects['modify'][$id] = $project;
                    }
                }
                else {
                    # Ignore duplicates
                    if (isset($projects['create'][$id])){
                        $this->output->writeError(
                            "Unable to proccess project '$id' at '{$project['_path']}', " .
                            "another project with the same name is already exist at '{$projects['create'][$id]['_path']}'."
                        );

                        continue;
                    }

                    $project['_created'] = time();
                    $project['_modified'] = time();
                    $projects['create'][$id] = $project;
                }

                if(isset($projects_catalog[$id])){
                    unset($projects_catalog[$id]);
                }
            }
        }

        # Prevent project duplicates
        foreach($projects['create'] as $id => $project){
            foreach(['skip', 'check', 'modify'] as $action){
                if (isset($projects[$action][$id])){
                    $this->output->writeError(
                        "Unable to proccess project '$id' at '{$project['_path']}', " .
                        "another project with the same name is already exist at '{$projects[$action][$id]['_path']}'."
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

        $projects_total = (
            count($projects['skip'])   +
            count($projects['check'])  +
            count($projects['modify']) +
            count($projects['create']) +
            count($projects['delete'])
        );

        if($projects_total > count($projects['skip'])){
            $this->output->writeInfo(
                "Found " . $projects_total . " project(s)" .
                (count($projects['skip'])   > 0 ? "\n       |--> " . (count($projects['skip']))   . " will be skipped" : "") .
                (count($projects['check'])  > 0 ? "\n       |--> " . (count($projects['check']))  . " don't have changes but needs to be checked" : "") .
                (count($projects['create']) > 0 ? "\n       |--> " . (count($projects['create'])) . " will be created" : "") .
                (count($projects['modify']) > 0 ? "\n       |--> " . (count($projects['modify'])) . " will be modified" : "") .
                (count($projects['delete']) > 0 ? "\n       |--> " . (count($projects['delete'])) . " will be deleted" : "")
            );
        }
        else {
            $this->output->writeDebug("Found $projects_total project(s)");
        }
    }

    /**
     * Tells whether a project needs to be checked due to other relevant changes
     *
     * @param $id
     * @param $project
     * @return bool
     */
    protected function __projectNeedsCheck($id, $project){

        // Re-check if php::builds config has been changed
        if(!empty($project['vhost']) && !empty($project['php'])){
            return Config::hasChanges('php::builds');
        }

        return false;
    }
}
