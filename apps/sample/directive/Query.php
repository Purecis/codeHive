<?php

namespace App\Directive;

use App\System\Directive;
use App\System\response;

class Query extends Directive
{
    public function __bootstrap(){
        // echo "bootstrap";
    }
    public function handle(Directive $directive)
    {
        return "called from query " . $this->content;
    }
}
