<?php

namespace App\Resource\jQuery\Middleware;

use App\System\Middleware;
use App\System\response;

class Tot extends Middleware
{
    public function handle(Response $response)
    {
        
        // return $this->response("killed by Auth");
        // return $response("killed by Tot");

        return $this->next();
    }
}
