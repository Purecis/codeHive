<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Directives Module
 *
 * Append Special Shortcodes to the core
 *
 * @package		codeHive
 * @subpackage	Module
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 *
 *
 * For Nasted directives
 * Spacial Thanx to http://stackoverflow.com/questions/5438133/how-to-remove-improper-nesting-bbcode-tags-using-php
 * and http://www.amazon.com/Mastering-Regular-Expressions-Jeffrey-Friedl/dp/0596528124
 * $re_url = '%# Match outermost [URL=...]...[/URL] (may have nested URL tags
 *   (\[URL\b[^[\]]*+\])       # $1: opening URL tag.
 *   (                         # $2: Contents of URL tag.
 *     (?:                     # Group of contents alternatives.
 *       (?:(?!\[/?URL\b).)++  # One or more non-"[URL", non-"[/URL"
 *     | (?R)                  # Or recursively match nested [URL]..[/URL].
 *     )*+                     # Zero or more contents alternatives.
 *   )                         # End $2: Contents of URL tag.
 *   (\[/URL\s*+\])            # $3: Outermost closing [/URL]
 *   %six';
 *
 */

class Directives{

	/**
	 * Directives bootstrap
	 *
	 * @access	public
	 * @return	string
	 */
	public static function __bootstrap(){

		/**
		* directive repeat
		* Description : repeat data
		*/
		self::register("repeat", function($args, &$scope) { // TODO : filter by ( like weither)
			//print_r($args);
			$str = "";

			foreach(Controller::$scope->{$args->items} as $k => $v){
				Controller::$scope->{$args->as} = $v;
				if(is_null($args->{"index"}))Controller::$scope->{"{$ex[0]}Index"} = $k;
				else Controller::$scope->{$args->{"index"}} = $k;
				Controller::$scope->__index = $k;
				$str .= Shortcode::trigger($args->content);
			}
			return $str;

			if($args->items){

				//if($args->match == $args->equal)return Shortcode::trigger($args->content);
			}

		});

		/**
		* directive Each
		* Description : Looping array
		*/
		Shortcode::register(array(
			'code' 		=> 'each',
			'pattern' 	=> '%\<EACH (\b[^<\>]*+)\>((?:(?:(?!\</?EACH\b).)++| (?R))*+)(\</EACH\s*+\>)%six',
			'callback' 	=> function($match){
				$str = "";
				$ex = explode(' in ',trim($match[1]));
				foreach(Controller::$scope->$ex[1] as $k => $file){
					Controller::$scope->$ex[0] = $file;
					$index = "{$ex[0]}Index";
					Controller::$scope->$index = $k;
					Controller::$scope->__index = $k;
					$str .= Shortcode::trigger($match[2]);
				}
				return $str;
			}
		));

		/**
		* directive Scope
		* Description : Scoping variables
		*/
		Shortcode::register(array(
			'code' 		=> 'scope',
			'pattern' 	=> '#\{-(.+)\-}#Usi',
			'callback' 	=> function($match){
				$filter = explode("|", $match[1]);//check ex for plugins like lower
				$match[1] = $filter[0];

				$val = Directives::scope($match[1]);

				if(isset($filter[1])){
					$f = trim($filter[1]);
					if($f == 'upper'){
						$val = strtoupper($val);
					}else if($f == 'escape'){
						$val = htmlspecialchars($val,ENT_QUOTES,'UTF-8');
					}
				}
				return $val;
			}
		));

		/**
		* directive Each
		* Description : Looping array
		*/
		Shortcode::register(array(
			'code' 		=> 'if',
			'pattern' 	=> '%\<IF (\b[^<\>]*+)\>((?:(?:(?!\</?IF\b).)++| (?R))*+)(\</IF\s*+\>)%six',
			'callback' 	=> function($match){

				$parse = function($v){
					if(strpos(trim($v),'"') === 0){
						$v = rtrim($v,'"');
						$v = ltrim($v,'"');
						return $v;
					}else if(strpos(trim($v),"'") === 0){
						$v = rtrim($v,"'");
						$v = ltrim($v,"'");
						return $v;
					}else if(trim($v) == "true"){
						return true;
					}else if(trim($v) == "false"){
						return false;
					}else if(trim($v) == "null"){
						return '';
					}else{
						return Directives::scope($v);
					}
				};
				$ok = true;

				if(strpos($match[1], "=") !== false){
					$ex = explode("=",trim($match[1]));

					if($parse($ex[0]) == $parse($ex[1]))return Shortcode::trigger($match[2]);

				}else if(strpos($match[1], "!=") !== false){
					$ex = explode("!=",trim($match[1]));
					if($parse($ex[0]) != $parse($ex[1]))return Shortcode::trigger($match[2]);

				}else if(strpos($match[1], "isset") !== false){
					if(strpos(trim($match[1]),"!") === 0)$not = 1;
					else $not = 0;

					$ex = substr(trim($match[1]), 6+$not, -1);//explode("isset",trim());

					if($not){
						if(is_null($parse($ex)))return Shortcode::trigger($match[2]);
					}else{
						if(!is_null($parse($ex)))return Shortcode::trigger($match[2]);
					}

				}

				return ;
				/*
				$str = "";
				$ex = explode(' in ',trim($match[1]));
				foreach(Controller::$scope->$ex[1] as $file){
					Controller::$scope->$ex[0] = $file;
					$str .= Shortcode::trigger($match[2]);
				}
				return $str;
				*/
			}
		));

		/*
		* directive Scope
		* Description : Scoping variables
		*
		Shortcode::register(array(
			'code' 		=> 'scope',
			'pattern' 	=> '#\<!(.+)\>#Usi',
			'callback' 	=> function($match){
				$val = Controller::$scope;
				$ex = explode(".", $match[1]);
				$val = isset($val->$ex[0])?$val->$ex[0]:null;
				if(sizeof($ex) > 1){
					foreach($ex as $k => $v){
						if($k == 0)continue;
						$val = isset($val[$v])?$val[$v]:null;
						if($val == null)break;
					}
				}
				return $val;
			}
		));
		*/

		/**
		* directive Import
		* Description : include view
		*/
		Shortcode::register(array(
			'code' 		=> 'import',
			'pattern' 	=> '#\@import\((.+)\)#Usi',
			'callback' 	=> function($match){
				return View::load($match[1]);// set additional import if
			}
		));

		/**
		* directive Event
		* Description : trigger event
		*/
		Shortcode::register(array(
			'code' 		=> 'event',
			'pattern' 	=> '#\@event\((.+)\)#Usi',
			'callback' 	=> function($match){
				return Event::trigger($match[1]);
			}
		));

		/**
		* directive Internationalization
		* Description : trigger event
		* Usage : __(You have :count messages and :days days :> {count:1,days:2} @ module.name)
		*/
		Shortcode::register(array(
			'code' 		=> 'internationalization',
			'pattern' 	=> '#\__\((.+)\)#Usi',
			'callback' 	=> function($match){
				$ar = array();
				$at = false;

				$ex = explode(":>", $match[1]);

				if(isset($ex[1])){
					$ex2 = explode("@", $ex[1]);
					$a = explode(",",substr(trim($ex2[0]), 1, -1));
					foreach($a as $v){
						$vv = explode(":", $v);
						$ar[$vv[0]] = $vv[1];
					}
					//
					if(isset($ex2[1]))$at = $ex2[1];
				}

				return Internationalization::translate(trim($ex[0]),$ar,$at);
			}
		));

/*
		Shortcode::register(array(
			'code' 		=> 'input',
			'pattern' 	=> '#\[input(.+)]#Usi',
			'callback' 	=> function($match){
				return "input is here";
			}
		));

		Shortcode::register(array(
			'code' 		=> 'extends',
			'pattern' 	=> '#\@extends\((.+)\)#Usi',
			'callback' 	=> function($match){
				return "you are extending {$match[1]}";
			}
		));
*/


		/**
		* directive approxlen
		* Description : shorten the text
		*/
		self::register("approxlen", function($args, &$scope) {
			if(!$args->length)$args->length = 200;
			if(!$args->append)$args->append = '...';
			return String::approxlen($args->content,$args->length,$args->append);
		});

		/**
		* directive whether
		* Description : check data
		*/
		self::register("whether", function($args, &$scope) { 	//neither // repeat
			//print_r($args);
			if($args->match){
				if($args->match == $args->equal)return Shortcode::trigger($args->content);
			}
			// TODO : lt, gt, gte, lte, eq, // extract variable by eval if needed
		});


	}

	private static function scope($v){
		if(is_numeric(trim($v)))return $v;
		$val = Controller::$scope;
		$ex = explode(".", $v);
		$val = isset($val->$ex[0])?$val->$ex[0]:null;
		if(sizeof($ex) > 1){
			foreach($ex as $k => $v){
				if($k == 0)continue;
				$val = (array)$val;
				$val = isset($val[$v])?$val[$v]:null;
				if($val == null)break;
			}
		}
		return is_string($val)?stripcslashes($val):$val;
	}

	public static function register($element, $cb){
		Shortcode::register(array(
			'code' 		=> "element_{$element}",
			'pattern' 	=> "%\<{$element} (\b[^<\>]*+)\>((?:(?:(?!\</?{$element}\b).)++| (?R))*+)(\</{$element}\s*+\>)%six",
			'callback' 	=> function($match) use ($cb, $element){
				$cls = new stdClass();
				$cls->content = $match[2];
				$el = "{$element}-content";
				Controller::$scope->$el = $match[2];
				if(!empty($match[1])){
					$atts = String::parse_attr($match[1]);
					foreach ($atts as $k => $v) {
						if((strpos($v, "'") !== false || strpos($v, '"') !== false)){ // string here
							$v = substr($v, 1, -1); // right left
						}else{
							$v = self::scope($v);//Controller::$scope->$v;
						}
						$cls->$k = $v;
						$el = "{$element}-{$k}";
						Controller::$scope->$el = $v;
					}
				}
				// whether
				if(!is_null($cls->whether)){
					if(!eval("return {$cls->whether};"))return;
				}
				// neither
				if(!is_null($cls->neither)){
					if(eval("return {$cls->neither};"))return;
				}

				if(is_callable($cb)){
					$call = call_user_func_array($cb, array($cls, &Controller::$scope));

				}else if(is_string($cb)){
					$call = View::load($cb);
				}else{
					//echo $match[2];
					$call = Shortcode::trigger($match[2]);
				}
				return Shortcode::trigger($call);
				// trim($match[1]); // the args inside
				//Controller::$scope->__index = $k; // loop on variables and set them in scope
				//$str = Shortcode::trigger($match[2]);// run the match 2
				//return $str;
			}
		));


	}

}

/* End of file Query.php */
/* Location: ./system/module/Query.php */
