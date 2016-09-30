<?php


namespace App\Develop;
use \App\System\Module;

class Console extends Module{
    function __bootstrap(){
        echo "hello console";


        // \App\System\Request::fetch("http://ipv4.download.thinkbroadband.com/5MB.zip", "5MB.zip", function($current, $total){
        //     echo "{$current} / {$total}<br>";
        // });

        if(!$this->isGitInstalled()){
            echo "you should install git before you can use console.";
        };
    }

    function isGitInstalled(){
        $code = "git version";
        $run = `$code`;

        $len = strlen($code);
        return strncmp($code, $run, $len) === 0;
    }
}