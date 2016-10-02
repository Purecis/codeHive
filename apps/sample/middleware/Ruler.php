<?

namespace App\Middleware;
use App\System\Middleware;


class Ruler extends Middleware{
    function __bootstrap(){
        // echo "user model called";
    }

    function handle(){
        // $this->response->header("plain");

        return $this->next();
    }
}