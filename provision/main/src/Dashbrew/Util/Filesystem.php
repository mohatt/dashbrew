<?php

namespace Dashbrew\Util;

class Filesystem extends \Symfony\Component\Filesystem\Filesystem {

    public function mkdir ($dirs, $mode = 0777, $owner = null, $group = null) {
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

    public function copy ($originFile, $targetFile, $override = false, $owner = null, $group = null) {
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
}
