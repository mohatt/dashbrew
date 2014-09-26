<?php

namespace Dashbrew\Cli\Util;

use Symfony\Component\Filesystem\Exception\IOException;

class Filesystem extends \Symfony\Component\Filesystem\Filesystem {

    /**
     * @param array|string|\Traversable $dirs
     * @param int $mode
     * @param string $owner
     * @param string $group
     */
    public function mkdir($dirs, $mode = 0777, $owner = null, $group = null) {
        parent::mkdir($dirs, $mode);

        if(!empty($owner)){
            $this->chown($dirs, $owner, false);
            if(empty($group)){
                $group = $owner;
            }
        }

        if(!empty($group)){
            $this->chgrp($dirs, $group, false);
        }
    }

    /**
     * @param string $originFile
     * @param string $targetFile
     * @param bool $override
     * @param string $owner
     * @param string $group
     */
    public function copy($originFile, $targetFile, $override = false, $owner = null, $group = null) {
        parent::copy($originFile, $targetFile, $override);

        if(!empty($owner)){
            $this->chown($targetFile, $owner, false);
            if(empty($group)){
                $group = $owner;
            }
        }

        if(!empty($group)){
            $this->chgrp($targetFile, $group, false);
        }
    }

    /**
     * @param string $file
     * @param $content
     * @param string $owner
     * @param string $group
     */
    public function write($file, $content, $owner = null, $group = null) {

        $dir = dirname($file);
        if(!is_dir($dir)){
            $this->mkdir($dir);
        }

        $this->touch($file);

        $handle = fopen($file, 'w');
        if (!$handle) {
            throw new IOException(sprintf('Failed to open file "%s" for writing.', $file), 0, null, $file);
        }

        fwrite($handle, $content);
        fclose($handle);
        unset($handle);

        if (!is_file($file)) {
            throw new IOException(sprintf('Failed to write data to file "%s".', $file), 0, null, $file);
        }

        if(!empty($owner)){
            $this->chown($file, $owner, false);
            if(empty($group)){
                $group = $owner;
            }
        }

        if(!empty($group)){
            $this->chgrp($file, $group, false);
        }
    }
}
