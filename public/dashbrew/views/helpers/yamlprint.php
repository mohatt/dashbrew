<?php

function _yamlprint_string($hash, $key, $default = 'Undefined'){

    if(!isset($hash[$key])){
        $hash[$key] = $default;
    }

    return $hash[$key];
}

function _yamlprint_bool($hash, $key, $default = false){

    if(!isset($hash[$key])){
        $hash[$key] = $default;
    }

    if($hash[$key]){
        return 'Enabled';
    }

    return 'Disabled';
}

function _yamlprint_hash($hash, $key, $default = 'Undefined'){

    if(!isset($hash[$key])){
        $hash[$key] = $default;
    }

    if(!is_array($hash[$key])){
        return (string) $hash[$key];
    }

    $out = '';
    foreach($hash[$key] as $key => $value){
        $out .= '<u>' . $key . ':</u> ' . (string) $value . '<br/>';
    }

    return $out;
}

function _yamlprint_hash_array($hash, $key, $default = 'Undefined'){

    if(!isset($hash[$key])){
        $hash[$key] = $default;
    }

    if(!is_array($hash[$key])){
        return (string) $hash[$key];
    }

    $out = '';
    foreach($hash[$key] as $key => $value){
        if(!is_array($value)){
            continue;
        }

        $vout = '-';
        $n = 0;
        foreach($value as $vkey => $vvalue){
            if($n == 0){
                $vout .= '&nbsp;';
            }
            else {
                $vout .= '&nbsp;&nbsp;&nbsp;';
            }
            $vout .= '<u>' . $vkey . ':</u> ' . (string) $vvalue . '<br/>';
            $n++;
        }

        $out .= $vout;
    }

    return $out;
}

function _yamlprint_array($hash, $key, $default = 'Undefined'){

    if(!isset($hash[$key])){
        $hash[$key] = $default;
    }

    if(!is_array($hash[$key])){
        return (string) $hash[$key];
    }

    $out = '';
    foreach($hash[$key] as $value){
        $out .= '- ' . $value . '<br/>';
    }

    return $out;
}
