<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Validate.
 *
 * validator class
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class Validate
{

    public static $error = [];

    public static function check()
    {
        $args = func_get_args();
        $obj = new stdClass();
        $obj->status = true;
        $obj->messages = [];
        $obj->error = [];
        $obj->first = null;
        // $obj->last = null;

        foreach ($args[1] as $key => $value) {
            $value = explode('|', $value);
            foreach ($value as $check) {
                $check = explode(':', $check);
                //  d($check);
                //  echo "<br />";
                $args[0]->{$key} = isset($args[0]->{$key})?$args[0]->{$key}:null;
                $v = self::is($args[0]->{$key}, $check[0], isset($check[1]) ? $check[1] : null);
                if(!$v->status){
                    $obj->status = false;
                    if(!isset($obj->messages[$key]))$obj->messages[$key] = [];
                    array_push($obj->messages[$key], $v->error);
                    if(!isset($obj->error[$key]))$obj->error[$key] = $v->error;

                    $name = ucwords(preg_replace('/[A-Z]/', ' $0', $key));
                    if(!isset($obj->first))$obj->first = $name.' '.$v->error;
                    // break;

                    // array_push($obj->messages, $v->error);
                }

            }
        }
        s($obj);
        return $obj;
    }

    public static function is()
    {
        $args = func_get_args();

        $obj = new stdClass();
        $obj->status = false;

        switch ($args[1]) {
            case 'required':
                $obj->status = self::required($args[0]);
                $obj->error = 'Required';
                break;

            case 'isset':
                $obj->status = self::set($args[0]);
                $obj->error = 'Not Set';
                break;

            case 'min':
                $obj->status = self::min($args[0], $args[2]);
                $obj->error = "Length Should be Larger than {$args[2]}";
                break;

            case 'max':
                $obj->status = self::max($args[0], $args[2]);
                $obj->error = "Length Should be Less then {$args[2]}";
                break;

            case 'between':
                $obj->status = self::between($args[0], $args[2]);
                $obj->error = "Length Should be Between {$args[2]}";
                break;

            case 'exact-length':
                $obj->status = self::exact_length($args[0], $args[2]);
                $obj->error = "Length Sould be {$args[2]} Char";
                break;

            case 'equals':
                $obj->status = self::equals($args[0], $args[2]);
                $obj->error = "Not Equals to {$args[2]}";
                break;

            case 'equal-both':
                $obj->status = self::equal_both($args[0], $args[2]);
                $obj->error = "Should Equal one of {$args[2]}";
                break;

            case 'length-both':
                $obj->status = self::length_both($args[0], $args[2]);
                $obj->error = "Length Should be one of $args[2]";
                break;

            case 'gt':
                $obj->status = self::gt($args[0], $args[2]);
                $obj->error = "Should be between $args[2]";
                break;

            case 'gte':
                $obj->status = self::gte($args[0], $args[2]);
                $obj->error = "Should be between $args[2]";
                break;

            case 'lt':
                $obj->status = self::lt($args[0], $args[2]);
                $obj->error = "Should be between $args[2]";
                break;

            case 'lte':
                $obj->status = self::lte($args[0], $args[2]);
                $obj->error = "Should be between $args[2]";
                break;

            case 'range':
                $obj->status = self::range($args[0], $args[2]);
                $obj->error = "Should be between $args[2]";
                break;

            case 'numeric':
                $obj->status = self::numeric($args[0]);
                $obj->error = 'Should be a Number';
                break;

            case 'integer':
                $obj->status = self::integer($args[0]);
                $obj->error = 'Not Valid Integer';
                break;

            case 'string':
                $obj->status = self::string($args[0]);
                $obj->error = 'Not Valid String';
                break;

            case 'float':
                $obj->status = self::float($args[0]);
                $obj->error = 'Not Valid Float';
                break;

            case 'alpha':
                $obj->status = self::alpha($args[0]);
                $obj->error = 'We Accept Alphabet Only';
                break;

            case 'alpha-numeric':
                $obj->status = self::alpha_numeric($args[0]);
                $obj->error = 'Alphabet and Numbers Allowed Only';
                break;

            case 'email':
                $obj->status = self::email($args[0]);
                $obj->error = 'Not Valid Email Address';
                break;

            case 'ip':
                $obj->status = self::ip($args[0]);
                $obj->error = 'Not Valid IP Address';
                break;

            case 'url':
                $obj->status = self::url($args[0]);
                $obj->error = 'Not Valid URL';
                break;

            case 'exist':
                $obj->status = self::exist($args[0], $args[2], $k);
                $obj->error = 'Allready Exist in Database';
                break;

            case 'notexist':
                $obj->status = self::notexist($args[0], $args[2], $k);
                $obj->error = 'Not Exist in Database';
                break;

            default:
                # code...
                break;
        }

        return $obj;
    }

    protected static function set($input = null)
    {
        return isset($input);
    }

    protected static function required($input = null)
    {
        return !is_null($input) && (trim($input) != '');
    }

    protected static function numeric($input)
    {
        return is_numeric($input);
    }

    protected static function email($input)
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL);
    }

    protected static function integer($input)
    {
        return is_int($input) || ($input == (string) (int) $input);
    }

    protected static function string($input)
    {
        return is_string($input) || ($input == (string) $input);
    }

    protected static function float($input)
    {
        return is_float($input) || ($input == (string) (float) $input);
    }

    protected static function alpha($input)
    {
        return preg_match('#^[a-zA-Z]+$#', $input) == 1;
    }

    protected static function alpha_numeric($input)
    {
        return preg_match('#^[a-zA-Z0-9]+$#', $input) == 1;
    }

    protected static function ip($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP);
    }

    protected static function url($input)
    {
        return filter_var($input, FILTER_VALIDATE_URL);
    }

    protected static function max($input, $length)
    {
        return strlen($input) <= $length;
    }

    protected static function min($input, $length)
    {
        return strlen($input) >= $length;
    }

    protected static function range($input, $length)
    {
        $arr = explode(',', $param);

        return $input >= $arr[0] && $input <= $arr[1];
    }
    protected static function lt($input, $length)
    {
        return $input < $length;
    }
    protected static function lte($input, $length)
    {
        return $input <= $length;
    }
    protected static function gt($input, $length)
    {
        return $input > $length;
    }    protected static function gte($input, $length)
    {
        return $input >= $length;
    }

    protected static function between($input, $param)
    {
        $arr = explode(',', $param);

        return strlen($input) >= $arr[0] && strlen($input) <= $arr[1];
    }

    protected static function exact_length($input, $length)
    {
        return strlen($input) == $length;
    }

    protected static function equals($input, $param)
    {
        return $input == $param;
    }

    protected static function equal_both($input, $param)
    {
        $arr = explode(',', $param);

        return $input == $arr[0] || $input == $arr[1];
    }

    protected static function length_both($input, $param)
    {
        $arr = explode(',', $param);

        return strlen($input) == $arr[0] || strlen($input) == $arr[1];
    }

    protected static function exist($input, $table, $col)
    {
        database::query("SELECT count(*) as count from {$table} where {$col} = '{$input}';");

        return database::fetchColumn() > 0 ? true : false;
    }

    protected static function notexist($input, $table, $col)
    {
        return !self::exist($input, $table, $col);
    }
    // TODO : date check, dateISO, digits, creditcard, regix, phone, 
}

/* End of file Validate.php */
/* Location: ./system/module/Validate.php */
