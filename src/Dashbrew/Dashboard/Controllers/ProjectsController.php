<?php

namespace Dashbrew\Dashboard\Controllers;

use Dashbrew\Dashboard\Controller\Controller;
use Dashbrew\Cli\Util\Projects;

/**
 * Projects Controller Class
 *
 * @package Dashbrew\Dashboard\Controllers
 */
class ProjectsController extends Controller {

    protected function index() {

        $this->set('layout_title', 'Projects');
    }

    protected function widget($type) {

        $this->setLayout('ajax');

        switch($type){
            case 'grid':
                $projects = $this->__getProjects();
                break;
            case 'recent':
                $projects = $this->__getProjects(8, '_modified', 'desc');
                break;
            default:
                throw new \Exception("Unknow widget type supplied");
        }

        $this->set('projects', $projects);
        $this->render('widget/' . $type);
    }
    protected function info($id) {

        $this->setLayout('ajax');
        $this->set('id', $id);
        $this->set('project', $this->__getProject($id));
    }

    protected function __getProject($id){

        $projects = Projects::get();
        if(!isset($projects[$id])){
            return false;
        }

        return $this->__initProjectInfo($id, $projects[$id]);
    }

    protected function __getProjects($limit = null, $sort = 'title', $dir = 'asc'){

        $projects = Projects::get();
        foreach($projects as $id => $project){

            $projects[$id] = $this->__initProjectInfo($id, $project);
        }

        uasort($projects, function($a, $b) use($sort, $dir){
            if (($dir == 'asc' && $a[$sort] < $b[$sort]) || ($dir == 'desc' && $a[$sort] > $b[$sort])) {
                return -1;
            }
            if (($dir == 'asc' && $a[$sort] > $b[$sort]) || ($dir == 'desc' && $a[$sort] < $b[$sort])) {
                return 1;
            }
            return 0;
        });

        if(!empty($limit)){
            $projects = array_slice($projects, 0, $limit);
        }

        return $projects;
    }

    protected function __initProjectInfo($id, $project){

        if(empty($project['title'])){
            $project['title'] = $id;
        }

        $project['host'] = $id;
        if(empty($project['vhost']['servername'])){
            $project['host'] = $project['vhost']['servername'];
        }

        $project['http'] = 'http://' . $project['host'] . '/';
        if(!empty($project['vhost']['ssl'])){
            $project['https'] = 'https://' . $project['host'] . '/';
        }

        return $project;
    }
}
