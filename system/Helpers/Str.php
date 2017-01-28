<?php

namespace App\System;

class Str
{
    public static function escape($sHTML)
    {
        if (is_array($sHTML) || is_object($sHTML)) {
            return array_map(__METHOD__, $sHTML);
        }
        if (!empty($sHTML) && is_string($sHTML)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\32"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $sHTML);
        }
        return $sHTML;
    }
    /**
     * parse string with special char to variable
     * Example "my name is :name and i am :age years old." 
     *
     * @access	public
     * @param	integer         $text
     * @param	array|callable  $args
     * @return	string
     */
    public static function bindSyntax($text, $args, $regex = ':', $onEmpty='empty')
    {
        switch ($regex) {
            case ':':
            case 'colon':
                $regex = '/:(\w+)/'; // hello :name
                break;

            case '__()':
            case 'translation':
                $regex = '/__\((.+)\)/'; // hello __(name)
                break;

            case '{{}}':
            case 'mustache':
                $regex = '/{{(.+)}}/'; // hello {{name}}
                break;
            
            case '$':
            case 'variable':
                $regex = '/\${(\w+)}/'; // hello ${name}
                break;
            
            default:
                $regex = '/\\' . $regex . '{(\w+)}/'; // hello _{name} regex is any char instad $
                break;
        }

        // check if the args is string, then its a scope name
        if(is_string($args)){
            $args = new Scope($args);
        }
        
        if(is_callable($args)){
            $callable = call_user_func_array($args, $matches);
        }else{
            $callable = function ($matches) use($args, $onEmpty) {
                if(is_array($args)){
                    $args = (object) $args;
                }
                return $args->{$matches[1]} ? $args->{$matches[1]} : ($onEmpty=='same'?$matches[0]:'');
            };
        }

        return preg_replace_callback($regex, $callable, $text);
    }
    public static function contains($haystack, $needles)
    {
        return strpos($haystack, $needles) !== false;
    }
    public static function extractCase($str)
    {
        preg_match_all('/[a-z]+|[A-Z][a-z]*/',$str,$matches);
        return $matches[0];
    }




    /**
	 * Random String
	 *
	 * Generate Random String From Scratch
	 *
	 * @access	public
	 * @param	integer string length that will generate
	 * @param	string 	special characters that you want to generate from
	 * @return	string
	 */
	public static function random($len = 8,$characters = false){
		if(!$characters && $len < 32){
			return substr(md5(rand().rand()), 0, $len);
		}else{
			if($characters === false)$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			$randomString = '';
			for($i=0; $i<$len; $i++)$randomString .= $characters[rand(0, strlen($characters) - 1)];
			return $randomString;
		}
	}
}