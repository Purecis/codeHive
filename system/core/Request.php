<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Request Class
 *
 * This class control all the request attrbutes and protect 
 * them from known attac and injection
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Request{

	/**
	 * List of paths to load views from
	 *
	 * @var array
	 * @access protected
	 */
	protected 	static $base;
	protected 	static $aliasArr;
	protected 	static $alias;
	protected 	static $querystring;

	// --------------------------------------------------------------------

	/**
	 * Set Request parser
	 *
	 * Parsing the request url links
	 *
	 * @access	public
	 * @return	array
	 */
	public static function parser($ret=false){
		if(self::$aliasArr){
			if($ret === false)return self::$aliasArr;
			else return self::$aliasArr[$ret];
		}
		self::$base = rtrim($_SERVER['SCRIPT_NAME'],INDEX_FILE);
		$REQUEST_URI = $_SERVER['REQUEST_URI'];

		$REQUEST_URI_exp = explode('?',$REQUEST_URI);
		self::$querystring = sizeof($REQUEST_URI_exp)>1?$REQUEST_URI_exp[1]:null;

		$prefix = self::$base;
		$alias = $REQUEST_URI_exp[0];

		// remove base from alias
		if (substr($alias, 0, strlen($prefix)) == $prefix) {
		    $alias = substr($alias, strlen($prefix));
		}
		// remove index.php if found
		if (substr($alias, 0, strlen(INDEX_FILE)) == INDEX_FILE) {
		    $alias = substr($alias, strlen(INDEX_FILE));
		}
		$alias = ltrim($alias,"/");

		self::$alias = $alias?$alias:'index';

		$arr = explode("/", $alias);
		$arr = array_filter($arr);
		if(empty($arr[0]))$arr[0] = 'index';
		self::$aliasArr = $arr;

		if($ret === false)return self::$aliasArr;
		else return self::$aliasArr[$ret];
	}

	// --------------------------------------------------------------------
	
	public static function method(){
		$method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
		return $method;//$_SERVER['REQUEST_METHOD'];
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request path from root
	 *
	 * @access	public
	 * @param	mixen 	type (RELATIVE_PATH, ABSOLUTE_PATH, FULL_PATH)
	 * @param	boolean if you want to print it or not
	 * @return	string
	 */
	public static function path($type=1,$echo=false){
		$path = "";
		if($type == 2)$path .= self::domain(true);
		if($type == 2 or $type == 1)$path .= self::base(self::alias());
		else $path = self::alias();
		return $path;
	}

	// --------------------------------------------------------------------

	/**
	 * site ( Alias )
	 *
	 * @access	public
	 * @param	mixen 	type (RELATIVE_PATH, ABSOLUTE_PATH, FULL_PATH)
	 * @param	boolean if you want to print it or not
	 * @return	string
	 */
	public static function site($type=1,$echo=false){
		return self::path($type,$echo);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Domain name
	 *
	 * @access	public
	 * @param	boolean use http, https
	 * @param	string 	suffix
	 * @param	boolean if you want to print it or not
	 * @return	string
	 */
	public static function domain($prefix=false,$suffix=false,$echo=false){
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
		$domain = $_SERVER['HTTP_HOST'];
		if($prefix)$domain = $protocol.$domain;
		if($suffix)$domain = $domain.$suffix;
		if($echo)echo $domain;

		return $domain;
	}

	// --------------------------------------------------------------------

	/**
	 * Get IP
	 *
	 * @access	public
	 * @return	string
	 */
	public static function ip(){
		 return $_SERVER['REMOTE_ADDR'];
	}
	// --------------------------------------------------------------------

	/**
	 * Absolute Path
	 *
	 * @access	public
	 * @param	string 	suffix
	 * @param	boolean if you want to print it or not
	 * @return	string
	 */
	public static function base($suffix=false,$echo=false){
		self::parser();
		$base = self::$base;
		if($suffix)$base = $base.$suffix;
		if($echo)echo $base;
		return $base;
	}

	// --------------------------------------------------------------------

	/**
	 * Alias Path
	 *
	 * @access	public
	 * @param	string 	suffix
	 * @param	boolean if you want to print it or not
	 * @return	string
	 */
	public static function alias($suffix=false,$echo=false){
		self::parser();
		$alias = self::$alias;
		if($suffix)$alias = $alias.$suffix;
		if($echo)echo $alias;
		return $alias;
	}

	// --------------------------------------------------------------------

	/**
	 * Querystring Parser
	 *
	 * Update or replace variable in Querystring
	 *
	 * @access	public
	 * @param	array 	variables that you want to update or add, leave it getting
	 * @param	boolean if you want to print it or not
	 * @return	string
	 */
	public static function querystring($args=false,$echo=false){
		self::parser();
		$querystring = self::$querystring;
		if($args){
			foreach($args as $param => $value){
				$value = urlencode($value);
				$querystring = self::updateQuerystring($querystring,$param,$value);
			}
		}
		return $querystring;
	}

	// --------------------------------------------------------------------

	/**
	 * Querystring Updater
	 *
	 * Update or replace variable in Querystring
	 *
	 * @access	private
	 * @param	string 	querystring
	 * @param	string 	param to add or replace
	 * @param	string 	value for param that added or replaced
	 * @return	string
	 */
	private static function updateQuerystring($querystring, $param, $value){
		$f = array();
		$ex = explode("&", $querystring);
		$founded=false;
		foreach($ex as $arg){
			$a = explode("=", $arg);
			if($a[0] == $param){
				$a[1] = $value;
				$founded = true;
			}
			array_push($f,implode("=", $a));
		}
		if($founded == false)array_push($f,"{$param}={$value}");
		return implode("&", $f);
	}

	// --------------------------------------------------------------------

	/**
	 * Variable Getter
	 *
	 * Geting variables from alias,querystring,post and get
	 *
	 * @access	public
	 * @param	string 	variable name
	 * @param	boolean if the request is array or not
	 * @return	mixen
	 */
	public static function get($name=0){
		//check array type
		//if($getArray)return $_REQUEST[$name];// return array without parsing

		if(is_array($name)){
			$cls = new stdClass();
			foreach($name as $n){
				$ex = explode(":", $n);
				$ex = $ex[0];
				$cls->$ex = self::get($n);
			}
			return $cls;
		}

		$type = false;
		if(strpos($name,':') !== false){
			$ex = explode(":", $name);
			$name = $ex[0];
			$type = $ex[1];
		}

		$url = self::parser();
		$ret = false;
		if($name == 'pagename')return $url[0];

		// check Router
		/*
		if($name !== 0 && sizeof(self::$router[$url[0]]) > 0){
			$arrSearch = array_search($name, self::$router[$url[0]]);
			if($arrSearch !== false){
				$ret = $url[$arrSearch+1];
			}
		}
		*/

		// check GET & POST
		if(!$ret){
			if(isset($_REQUEST[$name])){
				$ret = $_REQUEST[$name];
				if($type == 'json'){
					$json = (array)json_decode($ret);
					if(!$json)$ret = explode(",", $ret);
					else $ret = $json;
				}
			}else{
				if($type == 'json')$ret = array();
			}
		}

		// escape string
		if($ret){
		//	if(is_array($ret))echo "$name is array";
		//		$ret = implode(",",$ret);
		//	}else{
				$ret = String::escape($ret);				
		//	}
		}

		return $ret;
	}
	
	// $_SERVER['REQUEST_METHOD'] for restfull api GET, POST, PUT or DELETE)
}

/**
 * Define path methods
 */
define('RELATIVE_PATH'	, 0);// just alias
define('ABSOLUTE_PATH'	, 1);// main folder with alias
define('FULL_PATH'		, 2);// domain and main folder and alias


/* End of file Request.php */
/* Location: ./system/core/Request.php */