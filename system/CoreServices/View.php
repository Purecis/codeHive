<?php
namespace App\System;

class View
{
    
    public static function inject($view, $scope=''){

        $view = str_replace(".", "/", $view);
        $view = Loader::getDir('view/' . $view . ".html");
        $view = file_get_contents($view);
        
        return Directive::trigger($view, $scope);
    }
}
