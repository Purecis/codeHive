<?php

namespace App\Middleware;

use App\System\Middleware;
use App\System\response;

class Auth extends Middleware
{
    public function handle(Response $response)
    {
        
        // return $this->response("killed by Auth");
        // return $response("killed by Auth");

        return $this->next();
    }
}
