<?php

namespace Dashbrew\Cli\Util;

/**
 * Projects Class.
 *
 * @package Dashbrew\Util
 */
/**
 * Class Projects
 * @package Dashbrew\Cli\Util
 */
class Projects {

    /**
     * The path to the file that holds informations about installed projects
     */
    const PROJECTS_CATALOG_FILE  = '/vagrant/provision/main/etc/projects.json';

    /**
     * The path to the file that contains the hosts that needs to be imported into /etc/hosts
     */
    const PROJECTS_HOSTS_FILE    = '/vagrant/provision/main/etc/hosts.json';

    /**
     * The path to the file that contains the defined projects shortcuts
     */
    const PROJECTS_SHORTCUTS_FILE    = '/vagrant/provision/main/etc/shortcuts.json';

    /**
     * @var array
     */
    protected static $projects;

    /**
     * @var array
     */
    protected static $hosts;

    /**
     * @var array
     */
    protected static $shortcuts;

    /**
     * Loads projects catalog file
     */
    public static function init() {

        self::$projects = [];
        if(file_exists(self::PROJECTS_CATALOG_FILE)){
            self::$projects = json_decode(file_get_contents(self::PROJECTS_CATALOG_FILE), true);
        }
    }

    /**
     * Loads projects shortcuts file
     */
    public static function initShortcuts() {

        self::$shortcuts = [];
        if(file_exists(self::PROJECTS_SHORTCUTS_FILE)){
            self::$shortcuts = json_decode(file_get_contents(self::PROJECTS_SHORTCUTS_FILE), true);
        }
    }

    /**
     * Loads hosts file
     */
    public static function initHosts() {

        self::$hosts = [];
        if(file_exists(self::PROJECTS_HOSTS_FILE)){
            self::$hosts = json_decode(file_get_contents(self::PROJECTS_HOSTS_FILE), true);
        }
    }

    /**
     * Fetches a single project or a list of all projects
     *
     * @param string $id
     * @return mixed
     */
    public static function get($id = null) {

        if(!isset(self::$projects)){
            self::init();
        }

        if($id !== null){
            return self::$projects[$id];
        }

        return self::$projects;
    }

    /**
     * Fetches defined project shortcuts
     *
     * @return mixed
     */
    public static function getShortcuts() {

        if(!isset(self::$shortcuts)){
            self::initShortcuts();
        }

        return self::$shortcuts;
    }

    /**
     * Fetches all hosts
     *
     * @return mixed
     */
    public static function getHosts() {

        if(!isset(self::$hosts)){
            self::initHosts();
        }

        return self::$hosts;
    }

    /**
     * Fetches projects count
     *
     * @return int
     */
    public static function getCount() {

        if(!isset(self::$projects)){
            self::init();
        }

        return count(self::$projects);
    }

    /**
     * Writes projects hosts file
     *
     * @param $catalog
     * @return bool
     */
    public static function writeCatalog($catalog) {

        $file = self::PROJECTS_CATALOG_FILE;
        $content = json_encode($catalog);
        if(!file_exists($file) || md5($content) !== md5_file($file)){
            $fs = Util::getFilesystem();
            $fs->write($file, $content, 'vagrant');
            return true;
        }

        return false;
    }

    /**
     * Writes projects catalog file
     *
     * @param $hosts
     * @return bool
     */
    public static function writeHosts($hosts) {

        $file = self::PROJECTS_HOSTS_FILE;
        $content = json_encode($hosts);
        if(!file_exists($file) || md5($content) !== md5_file($file)){
            $fs = Util::getFilesystem();
            $fs->write($file, $content, 'vagrant');
            return true;
        }

        return false;
    }

    /**
     * Writes projects shortcuts file
     *
     * @param $shortcuts
     * @return bool
     */
    public static function writeShortcuts($shortcuts) {

        $file = self::PROJECTS_SHORTCUTS_FILE;
        $content = json_encode($shortcuts);
        if(!file_exists($file) || md5($content) !== md5_file($file)){
            $fs = Util::getFilesystem();
            $fs->write($file, $content, 'vagrant');
            return true;
        }

        return false;
    }
}
