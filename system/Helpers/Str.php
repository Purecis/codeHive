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
     * parse string with colon variables
     * Example "my name is :name and i am :age years old." 
     *
     * @access	public
     * @param	integer  $text
     * @param	array    $args
     * @return	string
     */
    public static function parseColon($text, $args)
    {
        return preg_replace_callback('/:(\\w+)/', function ($matches) use($args) {
            return isset($args[$matches[1]]) ? $args[$matches[1]] : $matches[0];
        }, $text);
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