<?php

namespace Dashbrew\Cli\Util;

class SyncManager extends Registry {

    private static $key = 'sync';
    private static $index = [];

    const SYNC_FILE = 'file';
    const SYNC_DIR  = 'dir';

    public static function addRule($type, $rule) {

        static $i = 0;

        if(!self::check(self::$key)){
            self::set(self::$key, []);
        }

        if(empty($rule['source']) || empty($rule['path']) || empty($rule['owner']) || empty($rule['group'])){
            throw new \Exception ("Invalid sync rule supplied to SyncManager::addRule");
        }

        $rule['type']   = $type;
        $rule['source'] = rtrim($rule['source'], " /");
        $rule['path']   = rtrim($rule['path'], " /");

        if(isset(self::$index[$rule['source']])){
            throw new \Exception ("Duplicate sync rule (source: $rule[source]) supplied to SyncManager::addRule");
        }

        if(isset(self::$index[$rule['path']])){
            throw new \Exception ("Duplicate sync rule (source: $rule[path]) supplied to SyncManager::addRule");
        }

        $rules = self::get(self::$key);

        $i++;
        $rules[$i] = $rule;
        self::$index[$rule['source']] = $i;
        self::$index[$rule['path']] = $i;

        self::set(self::$key, $rules);

        return $i;
    }

    public static function removeRule($path, $recursive = false, $removeSource = false, $removeTarget = false) {

        if(!self::check(self::$key)){
            return;
        }

        $path = rtrim($path, " /");
        $remove = [];
        foreach(self::$index as $index_path => $i){
            if($index_path == $path || ($recursive && 0 === strpos($index_path, $path, 0))){
                $remove[] = $i;
            }
        }

        $fs = Util::getFilesystem();
        $rules = self::get(self::$key);
        foreach(array_unique($remove) as $i){
            if($removeSource){
                $fs->remove($rules[$i]['source']);
            }

            if($removeTarget){
                $fs->remove($rules[$i]['path']);
            }

            unset(self::$index[$rules[$i]['source']]);
            unset(self::$index[$rules[$i]['path']]);
            unset($rules[$i]);
        }

        self::set(self::$key, $rules);
    }

    public static function getRules($type = null) {

        if(!self::check(self::$key)){
            return [];
        }

        $rules = self::get(self::$key);
        if($type === null){
            return $rules;
        }

        $filtered = [];
        foreach($rules as $i => $rule){
            if($rule['type'] == $type){
                $filtered[$i] = $rule;
            }
        }

        return $filtered;
    }
}
