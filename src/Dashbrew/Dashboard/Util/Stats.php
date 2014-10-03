<?php

namespace Dashbrew\Dashboard\Util;

use Dashbrew\Cli\Util\Util;
use Dashbrew\Cli\Util\Config;
use Dashbrew\Cli\Util\Projects;

class Stats {

    public static function getPhpsCount(){

        return count(Util::getInstalledPhps()) + 1;
    }

    public static function getDatabaseCount(){

        $mysqli = new \mysqli("localhost", "root", "root");
        if ($mysqli->connect_errno) {
            return false;
        }

        $count = false;
        if ($result = $mysqli->query("SHOW DATABASES")) {
            $count = $result->num_rows;
            $result->close();
        }

        $mysqli->close();

        return $count;
    }

    public static function getProjectCount(){

        return Projects::getCount();
    }

    public static function getUptime($simple = true){

        exec('uptime -s', $output, $return_var);
        if($return_var !== 0){
            return false;
        }

        if(ini_get('date.timezone') == ''){
            date_default_timezone_set('UTC');
        }

        $date = new \DateTime($output[0]);
        $interval = $date->diff(new \DateTime());
        if(!$simple){
            return $interval;
        }

        $units = ['d', 'h', 'i', 's'];
        $diff = [];
        foreach($units as $i => $u){
            if($interval->$u){
                if($i != 0 && isset($prev_u) && $prev_u != $units[$i-1]){
                    break;
                }

                $diff[] = $interval->$u . ($u == 'i' ? 'm' : $u);
                if(count($diff) == 2){
                    break;
                }

                $prev_u = $u;
            }
        }

        return $diff;
    }
}
