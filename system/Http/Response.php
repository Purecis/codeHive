<?php

namespace App\System;

class Response
{
    public $content = null;
    public $type = "html";
    public $headerSended = false;

    public function header($type = false)
    {
        if($type){
            $this->type = $set;
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

        if ($this->type == "json") {
            $this->content = json_encode($data, JSON_NUMERIC_CHECK);
        }
        
        return $this;
    }
    
    public function json()
    {
        $this->type = "json";
        $this->header();
        return $this;
    }
    public function plain()
    {
        $this->type = "plain";
        $this->header();
        return $this;
    }

    public function redirect($to)
    {
        header('Location: ' . $to);
        $this->headerSended = true;
        return $this;
    }

    
    public function spread()
    {
        return print $this->content;
    }
    // download
    // file
    // redirect
    // append
    // prepend
    // use output cache
}
