<?php

namespace App\Resource\jQuery\Directive;

use App\System\Directive;
use App\System\response;

class QueryBuilder extends Directive
{
    // public function __bootstrap(){
    //     // echo "bootstrap";
    // }
    public function handle(Directive $directive)
    {
        return "called from query inside a plugin jquery " . $this->content;
    }
}
