<?php

namespace Dashbrew\Tasks;

use Dashbrew\Commands\ProvisionCommand;
use Dashbrew\Task\Task;
use Dashbrew\Util\Config;
use Dashbrew\Util\Util;
use Dashbrew\Util\Registry;

/**
 * ProjectsProcess Task Class.
 *
 * @package Dashbrew\Tasks
 */
class ProjectsProcessTask extends Task {

    /**
     * @throws \Exception
     */
    public function run() {

        if(!$this->command instanceof ProvisionCommand){
            throw new \Exception("The Config task can only be run by the Provision command.");
        }

        $projects = Registry::get('projects');

        if(count($projects['modify']) == 0 && count($projects['create']) == 0 && count($projects['delete']) == 0){
            return;
        }

        foreach(['modify', 'create', 'delete'] as $action){
            foreach($projects[$action] as $id => $project){
                if(!empty($project['vhost'])){
                    $this->processVhost($action, $id, $project);
                }
            }
        }
    }

    protected function processVhost($action, $id, $project) {

        $fs = Util::getFilesystem();
        $vhost = $project['vhost'];
        $vhost = array_merge([
            'docroot'         => '${dir}',
            'servername'      => $id,
            'options'         => ['Indexes','FollowSymLinks','MultiViews'],
            'override'        => ['None'],
            'directoryindex'  => '',
            'ssl'             => false,
            'ssl_cert'        => '/etc/ssl/certs/ssl-cert-snakeoil.pem',
            'ssl_key'         => '/etc/ssl/private/ssl-cert-snakeoil.key',
            'ssl_certs_dir'   => '/etc/ssl/certs',
            'php-fpm'         => '',
        ], $vhost);

        $vhost_file_path = "/etc/apache2/sites-enabled/{$id}.conf";
        $vhost_ssl_file_path = "/etc/apache2/sites-enabled/{$id}-ssl.conf";

        if($action == 'delete'){
            $this->output->writeInfo("Removing apache vhost for '$id'");
            $fs->remove($vhost_file_path);
            $this->output->writeInfo("Removing apache ssl vhost for '$id'");
            $fs->remove($vhost_ssl_file_path);
            return;
        }

        if($action == 'modify' && !$vhost['ssl']){
            $this->output->writeInfo("Removing apache ssl vhost for '$id'");
            $fs->remove($vhost_ssl_file_path);
        }

        // Defauly vhost directory
        if(empty($vhost['directories'])){
            $vhost['directories'] = [[
                'provider'       => 'directory',
                'path'           => $vhost['docroot'],
                'options'        => $vhost['options'],
                'allow_override' => $vhost['override'],
                'directoryindex' => $vhost['directoryindex'],
                'require'        => 'all granted',
            ]];
        }

        $phps = Util::getInstalledPhps();
        $phps_config = Config::get('php::builds');
        if(!empty($project['php'])){
            $php_version = $project['php'];
            $php_version_fpm_conf = '/etc/apache2/php/php-' . $php_version . '-fpm.conf';
            if(!in_array('php-' . $php_version, $phps) || !isset($phps_config[$php_version])){
                $this->output->writeError("Unable to use php version '$php_version' for project '$id', php isn't installed");
            }
            else if(empty($phps_config[$php_version]['fpm']['port'])){
                $this->output->writeError("Unable to use php version '$php_version' for project '$id', php-fpm port isn't configured");
            }
            else if(!file_exists($php_version_fpm_conf)){
                $this->output->writeError("Unable to use php version '$php_version' for project '$id', apache php-fpm config file '$php_version_fpm_conf' doesn't exist");
            }
            else {
                $vhost['includes'] = [
                    $php_version_fpm_conf
                ];
            }
        }

        foreach($vhost['directories'] as $key => $dir){
            if(empty($dir['path']) || empty($dir['provider'])){
                unset($vhost['directories'][$key]);
                continue;
            }

            if(!preg_match('(directory|location|files)', $dir['provider']))
                $dir['provider'] = 'directory';

            $vhost['directories'][$key]['provider'] = ucfirst(str_replace('match', 'Match', $dir['provider']));
        }

        $vhost['serveradmin'] = "admin@$vhost[servername]";
        $vhost['port'] = '80';
        $vhost['access_log'] = "/var/log/apache2/vhost-{$id}.access.log";
        $vhost['error_log'] = "/var/log/apache2/vhost-{$id}.error.log";

        $this->output->writeInfo("Writing apache vhost file for '$id'");
        $vhost = $this->__replace_vhost_vars($vhost, $project['_path']);
        $vhost_file = Util::renderTemplate('apache/vhost.php', [
            'vhost'              => $vhost,
            '_project_id'        => $id,
            '_project_file_path' => $project['_path'],
        ], false);

        if(!file_put_contents($vhost_file_path, $vhost_file)){
            $this->output->writeError("Unable to write '$vhost_file_path'");
        }

        if($vhost['ssl']){
            $vhost_ssl = $vhost;

            $vhost_ssl['port'] = '443';
            $vhost_ssl['access_log'] = "/var/log/apache2/vhost-{$id}-ssl.access.log";
            $vhost_ssl['error_log'] = "/var/log/apache2/vhost-{$id}-ssl.error.log";

            $this->output->writeInfo("Writing apache ssl vhost file for '$id'");
            $vhost_ssl_file = Util::renderTemplate('apache/vhost.php', [
                'vhost'              => $vhost_ssl,
                '_project_id'        => $id,
                '_project_file_path' => $project['_path'],
            ], false);

            if(!file_put_contents($vhost_ssl_file_path, $vhost_ssl_file)){
                $this->output->writeError("Unable to write '$vhost_ssl_file_path'");
            }
        }

    }

    protected function __replace_vhost_vars($vhost, $project_file_path) {

        $vars = [
            'dir' => str_replace('/vagrant/public', '/var/www', dirname($project_file_path)),
            'root' => '/var/www'
        ];

        $vars['dir_esc'] = preg_quote($vars['dir']);
        $vars['root_esc'] = preg_quote($vars['root']);

        $s = [];
        $r = [];
        foreach($vars as $varname => $varvalue){
            $s[] = '${' . $varname . '}';
            $r[] = strval($varvalue);
        }

        foreach(['docroot'] as $key){
            $vhost[$key] = str_replace($s, $r, $vhost[$key]);
        }

        foreach($vhost['directories'] as $key => $dir){
            if(isset($vhost['directories'][$key]['path'])){
                $vhost['directories'][$key]['path'] = str_replace($s, $r, $vhost['directories'][$key]['path']);
            }
        }

        return $vhost;
    }

}
