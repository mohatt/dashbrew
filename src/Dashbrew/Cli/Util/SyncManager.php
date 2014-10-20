<?php

namespace Dashbrew\Cli\Util;

class SyncManager extends Registry {

    private static $key = 'sync';

    const SYNC_FILE = 'file';
    const SYNC_DIR  = 'dir';

    public static function addRule($type, $rule) {

        if(!self::check(self::$key)){
            self::set(self::$key, []);
        }

        if(empty($rule['source']) || empty($rule['path']) || empty($rule['owner']) || empty($rule['group'])){
            throw new \Exception ("Invalid sync rule supplied to SyncManager::addRule");
        }

        $id = rtrim($rule['source'], " /");
        $rules = self::get(self::$key);
        if(isset($rules[$id])){
            throw new \Exception ("Duplicate sync rule (source: $rule[source]) supplied to SyncManager::addRule");
        }

        $rule['type'] = $type;
        $rules[$id] = $rule;

        self::set(self::$key, $rules);
    }

    public static function removeRule($source, $removeSource = false, $removeTarget = false) {

        if(!self::check(self::$key)){
            return;
        }

        $id = rtrim($source, " /");
        $fs = Util::getFilesystem();
        $rules = self::get(self::$key);
        if(isset($rules[$id])){
            if($removeSource){
                $fs->remove($rules[$id]['source']);
            }

            if($removeTarget){
                $fs->remove($rules[$id]['path']);
            }

            unset($rules[$id]);
        }

        self::set(self::$key, $rules);
    }

    public static function removeRules($prefix, $removeSource = false, $removeTarget = false) {

        if(!self::check(self::$key)){
            return;
        }

        $prefix = rtrim($prefix, " /");
        $rules = self::get(self::$key);
        foreach(array_keys($rules) as $id){
            if(0 === strpos($id, $prefix, 0)){
                self::removeRule($id, $removeSource, $removeTarget);
            }
        }
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
        foreach($rules as $id => $rule){
            if($rule['type'] == $type){
                $filtered[$id] = $rule;
            }
        }

        return $filtered;
    }
}
