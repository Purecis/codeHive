<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis String Class
 *
 * This class Parse all String and formats.
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class String{

	/**
	 * Escaping Strings
	 *
	 * @access	public
	 * @param	mixen 	HTML, Text and Arrays to escape them
	 * @param	boolean use special character escaping
	 * @return	mixen
	 */
	public static function escape($sHTML,$extra=false){
		if(is_array($sHTML) || is_object($sHTML)){
			$arr = array();
			foreach($sHTML as $k => $v){
				$arr[$k] = self::escape($v,$extra);
			}
			return $arr;
		}
		if(strlen($sHTML) != '0'){
			if($extra){
				 $sHTML=str_replace("&","&amp;",$sHTML);
				 $sHTML=str_replace("<","&lt;",$sHTML);
				 $sHTML=str_replace(">","&gt;",$sHTML);
				 $sHTML=str_replace('"',"&quot;",$sHTML);
				 $sHTML=str_replace("'","&#39;",$sHTML);
			 }
			 //$sHTML=str_replace(" ","-",$sHTML);
			 //$sHTML=str_replace("%20","-",$sHTML);
			 $sHTML = trim($sHTML);
			 //$sHTML = mysql_real_escape_string($sHTML);
			 $sHTML = addslashes($sHTML);
			 //$sHTML=htmlspecialchars($sHTML);
		}else{
			$sHTML = null;
		}
		return $sHTML;
    }

	// --------------------------------------------------------------------

	/**
	 * String Parser ( Alias )
	 *
	 * Prepare String that you get from database
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
    public static function parse($html){
    	return self::decode($html);
    }

	// --------------------------------------------------------------------

	/**
	 * String Encode
	 *
	 * Prepare String to send to database
	 *
	 * @access	public
	 * @param	string text to encode
	 * @param	boolean use special character escaping
	 * @param	string charset type ex ( UTF-8, Windows-1256 )
	 * @return	string
	 */
	public static function encode($sHTML, $other=false, $type='utf-8'){
		if(strlen($sHTML) != '0'){
			if($type != 'utf-8')$sHTML = iconv($type ,'utf-8',$sHTML);
			if($other){
				 $sHTML=str_replace("&","&amp;",$sHTML);
				 $sHTML=str_replace("<","&lt;",$sHTML);
				 $sHTML=str_replace(">","&gt;",$sHTML);
				 $sHTML=str_replace('"',"&quot;",$sHTML);
				 $sHTML=str_replace("'","&#39;",$sHTML);
			 }
			 $sHTML=htmlspecialchars($sHTML);
		}else{
			$sHTML = null;
		}
    	return $sHTML;
    }

	// --------------------------------------------------------------------

	/**
	 * String Decode
	 *
	 * Prepare String that you get from database
	 *
	 * @access	public
	 * @param	string text to encode
	 * @param	boolean use special character escaping
	 * @param	string charset type ex ( UTF-8, Windows-1256 )
	 * @return	string
	 */
	public static function decode($sHTML,$other=false,$type='utf-8'){
		if($type != 'utf-8')$sHTML = iconv($type ,'utf-8',$sHTML);
		if($other){
			$sHTML=str_replace("&amp;","&",$sHTML);
			$sHTML=str_replace("&lt;","<",$sHTML);
			$sHTML=str_replace("&gt;",">",$sHTML);
			$sHTML=str_replace("&quot;",'"',$sHTML);
			$sHTML=str_replace("&#39;","'",$sHTML);
			$sHTML=str_replace("&rsquo;","'",$sHTML);
		}
		$sHTML = stripslashes($sHTML);
		$sHTML = htmlspecialchars_decode($sHTML);
    	return $sHTML;
	}

	// --------------------------------------------------------------------

	/**
	 * String Encrypt
	 *
	 * Secure and Encrypt String with secret key
	 *
	 * @access	public
	 * @param	string text to encrypt
	 * @param	string hash key
	 * @return	string
	 */
	public static function encrypt($string,$key=SECURITY_HASH){
	    $key = sha1($key);
	    $strLen = strlen($string);
	    $keyLen = strlen($key);
	    $j=null;
	    $hash='';
	    for ($i = 0; $i < $strLen; $i++) {
	        $ordStr = ord(substr($string,$i,1));
	        if ($j == $keyLen) { $j = 0; }
	        $ordKey = ord(substr($key,$j,1));
	        $j++;
	        $hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
	    }
	    return $hash;
	}

	// --------------------------------------------------------------------

	/**
	 * String Decrypt
	 *
	 * Decrypt Encrypted String with secret key
	 *
	 * @access	public
	 * @param	string text to decrypt
	 * @param	string hash key
	 * @return	string
	 */
	public static function decrypt($string,$key=SECURITY_HASH){
	    $key = sha1($key);
	    $strLen = strlen($string);
	    $keyLen = strlen($key);
	    $j=null;
	    $hash='';
	    for ($i = 0; $i < $strLen; $i+=2) {
	        $ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),36,16));
	        if ($j == $keyLen) { $j = 0; }
	        $ordKey = ord(substr($key,$j,1));
	        $j++;
	        $hash .= chr($ordStr - $ordKey);
	    }
	    return $hash;
	}

	// --------------------------------------------------------------------

	/**
	 * String MC Encrypt
	 *
	 * Secure and Encrypt String with secret key using mcrypt
	 *
	 * @access	public
	 * @param	string text to encrypt
	 * @param	string hash key
	 * @return	string
	 */
	public function mc_encrypt($encrypt, $key=SECURITY_HASH){
		$encrypt = serialize($encrypt);
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
		$key = pack('H*', $key);
		$mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
		$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
		$encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
		return $encoded;
	}

	// --------------------------------------------------------------------

	/**
	 * String MC Decrypt
	 *
	 * Decrypt Encrypted String with secret key using mcrypt
	 *
	 * @access	public
	 * @param	string text to decrypt
	 * @param	string hash key
	 * @return	string
	 */
	public function mc_decrypt($decrypt, $key=SECURITY_HASH){
		$decrypt = explode('|', $decrypt.'|');
		$decoded = base64_decode($decrypt[0]);
		$iv = base64_decode($decrypt[1]);
		if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
		$key = pack('H*', $key);
		$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
		$mac = substr($decrypted, -64);
		$decrypted = substr($decrypted, 0, -64);
		$calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
		if($calcmac!==$mac){ return false; }
		$decrypted = unserialize($decrypted);
		return $decrypted;
	}

	// --------------------------------------------------------------------

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
	public static function randomId($len = 8,$characters = false){
		if(!$characters && $len < 32){
			return substr(md5(rand().rand()), 0, $len);
		}else{
			if($characters === false)$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			$randomString = '';
			for($i=0; $i<$len; $i++)$randomString .= $characters[rand(0, strlen($characters) - 1)];
			return $randomString;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * String approx length
	 *
	 * Split Words of text based on characters
	 *
	 * @access	public
	 * @param	string
	 * @param	integer string length that will generate
	 * @param	string 	content that you want to append after
	 * @return	string
	 */
	public static function approxlen($str,$len=200,$append='...') {
		$str = strip_tags($str);
		$x = explode(" ",$str);
		$y = count($x);
		$newlen = '';
		for ($i = 0; $i < $y; $i++){
			$this_x = $x[$i]. ' ';
			if (strlen($newlen.$this_x) > $len) $i = $y;
			else $newlen = $newlen.$this_x;
		}
		if(trim($newlen) != trim($str))$newlen .= $append;
		return $newlen;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Attr
	 *
	 * Parse XML, Shortcode Attributes
	 *
	 * @access	public
	 * @param	string attribute string ex (name='abc' value='def')
	 * @return	array
	 */
	public static function parse_attr($att,$removeQ=false){
		$pattern = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
		preg_match_all($pattern, $att, $matches, PREG_SET_ORDER);
		$attrs = array();
		foreach ($matches as $match) {
			if (($match[2][0] == '"' || $match[2][0] == "'") && $match[2][0] == $match[2][strlen($match[2])-1] && $removeQ) {
				$match[2] = substr($match[2], 1, -1);
			}
			$name = strtolower($match[1]);
			$value = html_entity_decode($match[2]);
			switch ($name) {
			case 'class':
				$attrs[$name] = preg_split('/\s+/', trim($value));
				break;
			case 'style':
				// parse CSS property declarations
				$attrs[$name] = $value;
				break;
			default:
				$attrs[$name] = $value;
			}
		}
		return $attrs;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Attr
	 *
	 * Parse XML, Shortcode Attributes
	 *
	 * @access	public
	 * @param	string attribute string ex (name='abc' value='def')
	 * @return	array
	 */
	public static function xml_parse_attr($att){
		$att = self::decode($att,true);
		$x = new SimpleXMLElement("<element {$att} />");
		$attr = array();
		foreach($x->attributes() as $a => $b)$attr[$a] = trim($b);

		return $attr;
	}

	// --------------------------------------------------------------------

	/**
	 * json
	 *
	 * Parse XML, Shortcode Attributes
	 *
	 * @access	public
	 * @param	string attribute string ex (name='abc' value='def')
	 * @return	array
	 */
	public static function json($s){
        $s = str_replace(
            array('"',  "'"),
            array('\"', '"'),
            $s
        );
        $s = preg_replace('/(\w+):/i', '"\1":', $s);
		return is_array($s) ? $s : json_decode($s);
	}

	// --------------------------------------------------------------------

	/**
	 * ontime
	 *
	 * diffrent between 2 times
	 *
	 * @access	public
	 * @param	mixen datetime or unix time ex: (2016-02-22 22:25:43)
	 * @return	array
	 */
	public static function ontime($bef, $aft=false){
		if(!is_numeric($bef))$bef = strtotime($bef);
		if($aft == false)$aft = time();
		if(!is_numeric($aft))$aft = strtotime($aft);

		$timing = $aft-$bef;

		if(($timing/3600)<24){
			$h = floor($timing/3600);
			$i = floor(($timing-($h*3600))/60);

			if($i == 0){
				return __("Today")." ".__("A little while ago");
			}else{
				return __("Today")." ".__(":h Hour and :m Minute ago",['h'=>$h,'m'=>$i]);
			}

		}else if($timing <= 172800){
			$h = date("h",$bef);
			$i = date("i",$bef);
			$a = date('a',$bef);

			return __("Yesterday on :h::m :a",['h'=>$h,'m'=>$i,"a"=>$a]);

		}else{
			$days = array(
				__('Sunday'),
				__('Monday'),
				__('Tuesday'),
				__('Wednesday'),
				__('Thursday'),
				__('Friday'),
				__('Saturday')
			);
			$day = $days[date('w',$bef)];
			$date = date("d/m/Y");
			$time = date("h:i:s");
			$a = date('a',$bef);
			return __(":day :date on :time :a",["day"=>$day,"date"=>$date,"time"=>$time,"a"=>$a]);
		}
	}

}

/* End of file String.php */
/* Location: ./system/core/String.php */
