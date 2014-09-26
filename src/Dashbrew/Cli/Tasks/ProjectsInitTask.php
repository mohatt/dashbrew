<?php

namespace Dashbrew\Cli\Tasks;

use Dashbrew\Cli\Commands\ProvisionCommand;
use Dashbrew\Cli\Task\Task;
use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\Config;
use Dashbrew\Cli\Util\Registry;
use Dashbrew\Cli\Util\Finder;

/**
 * ProjectsInit Task Class.
 *
 * Finds and initializes projects defined under public/ directory.
 *
 * @package Dashbrew\Cli\Tasks
 */
class ProjectsInitTask extends Task {

    /**
     * The path to the file that contains the hosts that needs to be imported into /etc/hosts
     */
    const PROJECTS_HOSTS_FILE    = '/vagrant/provision/main/etc/hosts.json';

    /**
     * The path to the file that holds informations about installed projects
     */
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
            'skip'    => [],
            'check'   => [],
            'modify'  => [],
            'create'  => [],
            'delete'  => [],
        ];

        $yaml = Util::getYamlParser();
        $fs   = Util::getFilesystem();

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
                        if($this->__projectNeedsCheck($id, $project)){
                            $projects['check'][$id] = $project;
                        }
                        else {
                            $projects['skip'][$id] = $project;
                        }
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
            foreach(['skip', 'check', 'modify'] as $action){
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

        # Write hosts file so that it can be accessed later by the Hostmanager plugin
        $hosts_file = self::PROJECTS_HOSTS_FILE;
        $hosts_file_content = json_encode($hosts);
        if(!file_exists($hosts_file) || md5($hosts_file_content) !== md5_file($hosts_file)){
            $this->output->writeInfo("Writing hosts file");
            $fs->write($hosts_file, $hosts_file_content, 'vagrant');
        }

        # Write projects catalog file
        $projects_file = self::PROJECTS_CATALOG_FILE;
        $projects_file_content = json_encode(array_merge($projects['skip'], $projects['check'], $projects['modify'], $projects['create']));
        if(!file_exists($projects_file) || md5($projects_file_content) !== md5_file($projects_file)){
            $this->output->writeInfo("Writing projects catalog file");
            $fs->write($projects_file, $projects_file_content, 'vagrant');
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
