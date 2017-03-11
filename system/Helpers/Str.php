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
     * @param	string          $regex  the command prefix ${var}
     * @return	string                  parsed string
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
                $regex = '/__\((.+?)\)/'; // hello __(name)
                break;

            case '{{}}':
            case 'mustache':
                $regex = '/{{(.+?)}}/'; // hello {{name}}
                break;
            
            case '$':
            case 'variable':
                $regex = '/\${(.+?)}/'; // hello ${name}
                break;
            
            default:
                $regex = '/\\' . $regex . '{(.+?)}/'; // hello _{name} regex is any char instad $
                break;
        }

        // check if the args is string, then its a scope name
        if(is_string($args)){
            $args = new Scope($args);
        }
        
        // if you send a callable then parse using it
        if(is_callable($args)){
            $callable = $args;//call_user_func_array($args, $matches);
        }else{
            $callable = function ($matches) use($args, $onEmpty) {
                if(is_array($args)){
                    $args = (object) $args;
                }

                $param = trim($matches[1]);

                if(self::contains($param, '@')){
                    list($param, $scope) = explode('@', $param);
                    $args = new Scope($scope);
                }
                
                return $args->{$param} ? $args->{$param} : ($onEmpty=='same'?$matches[0]:'');
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

    public static function camelToSnake($str)
    {
        $class = Str::extractCase($str);
        $class = array_map('strtolower', $class);
        $class = implode('_', $class);
        return $class;
    }

    public static function miniPath($path, $size = 4){
        $path = explode("/", $path);
        if(sizeof($path) - $size > 0)$path = array_slice($path, sizeof($path) - $size);
        return implode("/", $path);
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