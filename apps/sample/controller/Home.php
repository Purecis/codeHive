<?php

namespace App\Controller;

use App\System\Controller;
use App\System\Response;
use App\System\Request;
use App\System\Route;

class Home extends Controller
{
    public function __bootstrap()
    {
        // echo "Home injected";
    }

    private static $counter = 0;
    
    public function tamer(Request $request, \App\Model\User $user, Route $router, Response $response)
    {
        return $response(123);
        // return $this->response("tamer");

        // print_r($router->params());
        // echo "tamer called";
        // return;
        // echo $request->domain;
        // echo $user->test();
        // echo $this->request->domain;


        // echo $this->request->http->referer;
        // return;

        // $this->ControllerHome->test(); // do it
        // $this->ModelUser->test();

        // $this->ResourceJQuery;
        // $this->ResourceJQueryModelUser;
        // $this->ResourceJQueryControllerUser->test();



        

        echo "<hr>";
         self::$counter ++;
        $name = "called num " . self::$counter;

        
        return $this->response->body($name)->spread();

        // return (new Response)->body($name)->spread();
        // return (new Response)->json()->body(["name" => "tamer"])->spread();
    }

    public function test(){
        echo "test called from home";
    }
    public function index(){
        echo "i am index";
    }
}
