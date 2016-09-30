<?

namespace App\Resource\jQuery\Model;

use App\System\Injectable;

class User extends Injectable{
    function __bootstrap(){
        echo "user called inside jQuery Model";
    }

    function test(){
        echo "test inside jquery model";
    }
}