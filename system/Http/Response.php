<?php

namespace App\System;

class Response
{
    public $content = null;
    public $type = "html";
    public $headerSended = false;

    public function __invoke(){ // called when the class name invoked
        $args = func_get_args();
        $this->body($args[0]);
        return $this;
    }

    public function header($type = false)
    {
        if($type){
            $this->type = $type;
        }

        if($this->headerSended){
            return $this;
        }
        
        // parse header by type
        if ($this->type == "json") {
            header('Content-Type: application/json');

        } elseif ($this->type == "plain") {
            header("Content-Type: text/plain");

        } elseif ($this->type == "404") {
            header("HTTP/1.0 404 Not Found");

        } else {
            header("Content-Type: text/html");

        }
        $this->headerSended = true;

        return $this;
    }

    public function body($data)
    {
        $this->content = $data;
        return $this;
    }
    
    public function json()
    {
        $this->type = "json";
        return $this;
    }
    public function plain()
    {
        $this->type = "plain";
        return $this;
    }

    public function redirect($to)
    {
        header('Location: ' . $to);
        $this->headerSended = true;
        return $this;
    }

    public function view($view){
        $this->content = View::inject($view);
        return $this;
    }
    
    public function spread()
    {
        if(is_array($this->content) || is_object($this->content)){
            $this->json();
        }
        
        $this->header();

        if ($this->type == "json") {
            $this->content = json_encode($this->content, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT);
        }

        return print $this->content;
    }
    // download
    // file
    // redirect
    // append
    // prepend
    // use output cache
}
