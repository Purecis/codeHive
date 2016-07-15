<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Shortcode Class
 *
 * control URL Parameters 
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Shortcode{

	/**
	 * List of arrays
	 *
	 * @access protected
	 */
	protected static $patterns = array();
	protected static $callback = array();

	// --------------------------------------------------------------------

	/**
	 * Shortcode Register
	 *
	 * @access	public
	 * @param	string 	shortcode regix 
	 * @return	void
	 */
	public static function register($sc){
		self::$patterns[$sc['code']] = $sc['pattern'];
		self::$callback[$sc['code']] = $sc['callback'];
	}

	// --------------------------------------------------------------------

	/**
	 * Shortcode trigger
	 *
	 * @access	public
	 * @param	string 	shortcode regix 
	 * @return	void
	 */
	public static function trigger($output,$custom = false){
		if(is_array($output) or is_object($output)){
			$out = Array();
			foreach($output as $k => $v){
				$out[$k] = self::trigger($v, $custom);
			}
			if(is_array($output))return $out;
			if(is_object($output))return (object) $out;
		}

		if($custom){
			if(!self::$patterns[$custom])return $output;
			return preg_replace_callback(self::$patterns[$custom], self::$callback[$custom] , $output);
		}

		foreach(self::$patterns as $k => $pat){
			$output = preg_replace_callback($pat, self::$callback[$k] , $output);
		}
		return $output;
	}

	// --------------------------------------------------------------------

	/**
	 * Shortcode Parse Atts
	 *
	 * Thanx for wordpress shortcode parse atts function (shortcode_parse_atts)
	 *
	 * @access	public
	 * @param	string 	shortcode regix 
	 * @return	void
	 */
	public static function parse($text){
		$atts = array();
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
		if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
			foreach ($match as $m) {
				if (!empty($m[1]))
					$atts[strtolower($m[1])] = stripcslashes($m[2]);
				elseif (!empty($m[3]))
					$atts[strtolower($m[3])] = stripcslashes($m[4]);
				elseif (!empty($m[5]))
					$atts[strtolower($m[5])] = stripcslashes($m[6]);
				elseif (isset($m[7]) and strlen($m[7]))
					$atts[] = stripcslashes($m[7]);
				elseif (isset($m[8]))
					$atts[] = stripcslashes($m[8]);
			}
		} else {
			$atts = ltrim($text);
		}
		return $atts;
	}
	
}

/* End of file Router.php */
/* Location: ./system/core/Router.php */