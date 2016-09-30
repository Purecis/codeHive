<?php

// echo "hello class";

namespace App\Resource;

// use App\Resource\jQuery\Model\User;
// use App\Resource\jQuery\Controller\User;
// use App\Resource\jQuery\Controller\User;

use \App\System\Injectable;

class JQuery extends Injectable implements jQueryInterface{
    function __construct(){
        parent::__construct();
    }
    function __bootstrap() // use DI
    {
        // echo " constructed\n";
        // new User();
        // new jQuery\Model\User();

        \App\System\Controller::inject("User@Resource.jQuery");
    }


}
