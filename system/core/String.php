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
	public static function parse_attr($att){
		$att = self::decode($att,true);
		$x = new SimpleXMLElement("<element {$att} />");
		$attr = array();
		foreach($x->attributes() as $a => $b)$attr[$a] = trim($b);
		
		return $attr;
	}
}

/* End of file String.php */
/* Location: ./system/core/String.php */