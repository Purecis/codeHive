<?php

namespace App\System;

class Response
{
    public $content = null;
    private $type = "html";
    private $headerSended = false;
    private $code = 200;

    public function __invoke()
    {
        $args = func_get_args();
        $this->body($args[0]);
        return $this;
    }

    public function header($type = false)
    {
        if ($type) {
            $this->type = $type;
        }

        if ($this->headerSended) {
            return $this;
        }
        
        // set current http response code
        http_response_code($this->code);

        // parse header by type
        if ($this->type == "json") {
            header('Content-Type: application/json');
        } elseif ($this->type == "plain") {
            header("Content-Type: text/plain");
        } else {
            header("Content-Type: text/html");
        }
        $this->headerSended = true;

        return $this;
    }
    
    public function code($code)
    {
        $this->code = $code;
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

    public function view($view)
    {
        $this->content = View::inject($view);
        return $this;
    }
    
    public function spread()
    {
        if ((is_array($this->content) || is_object($this->content))) {
            $this->json();
            $this->content = json_encode($this->content, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT);
        }
        
        $this->header();
        return print $this->content;
    }

    public function kill()
    {
        $this->spread();
        exit;
    }

    // download
    // file
    // redirect
    // append
    // prepend
    // use output cache
}
