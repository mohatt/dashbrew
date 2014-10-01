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
        $projects = $this->__getProjects();
        $this->set('projects', $projects);
    }

    protected function widget($type) {

        $this->setLayout('ajax');

        switch($type){
            case 'recent':
                $projects = $this->__getProjects(8, '_modified');
                $this->set('projects', $projects);
                break;
            default:
                throw new \Exception("Unknow widget type supplied");
        }

        $this->render('widget/' . $type);
    }

    protected function __getProjects($limit = null, $sort = 'title'){

        $projects = Projects::get();
        foreach($projects as $id => $project){
            if(empty($project['title'])){
                $projects[$id]['title'] = $id;
            }

            $projects[$id]['host'] = $id;
            if(empty($project['vhost']['servername'])){
                $projects[$id]['host'] = $project['vhost']['servername'];
            }

            $projects[$id]['http'] = 'http://' . $projects[$id]['host'] . '/';
            if(!empty($project['vhost']['ssl'])){
                $projects[$id]['https'] = 'https://' . $projects[$id]['host'] . '/';
            }
        }

        uasort($projects, function($a, $b) use($sort){
            if ($a[$sort] > $b[$sort]) {
                return -1;
            } else if ($a[$sort] < $b[$sort]) {
                return 1;
            } else {
                return 0;
            }
        });

        if(!empty($limit)){
            $projects = array_slice($projects, 0, $limit);
        }

        return $projects;
    }
}
