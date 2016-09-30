<?

namespace App\Model;
use App\System\Injectable;


class User extends Injectable{
    function __bootstrap(){
        echo "user model called";
    }

    function test(){
        echo "test from model";
    }
}