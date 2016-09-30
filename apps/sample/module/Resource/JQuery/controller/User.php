<?

namespace App\Resource\jQuery\Controller;

use App\System\Injectable;

class User extends Injectable{
    function __bootstrap(){
        echo "user injected inside jQuery Controller<br>";
    }

    public static function tamer(){
        echo "tamer inside jQuery";
    }

    public static function test(){
        echo "test inside jQuery";
    }
}