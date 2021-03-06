<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis File Class
 *
 * This class Control Files on the server.
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */
class File{

	/**
	 * File Format
	 *
	 * Calculate file format from Byte to MB,GB,TB and PB
	 *
	 * @access	public
	 * @param	integer File size in Bytes
	 * @return	string
	 */
	public static function format($size, $float=2){
	    $unit=array('Byte','KB','MB','GB','TB','PB');
		$n = round($size/pow(1024,($i=floor(log($size,1024)))),$float);
		if($float){
			$n = sprintf("%.{$float}f", $n);
		}
	    return $n.' '.$unit[$i];
	}

	// --------------------------------------------------------------------

	/**
	 * File Extension
	 *
	 * Extract Extension from string
	 *
	 * @access	public
	 * @param	integer File size in Bytes
	 * @return	string
	 */
	public static function extension($filename){
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}

	// --------------------------------------------------------------------

	/**
	 * Download Limiter
	 *
	 * Limit and protect downloadable files on the server
	 *
	 * @access	public
	 * @param	string 	Local File Location
	 * @param	string 	Download File name to save
	 * @param	integer Download Speed Rate (4096 => 400kbps | 4M Connection)
	 * @return	void
	 */
	public static function Download($local_file,$download_file='fileName',$download_rate=null){//4096
		if(file_exists($local_file) && is_file($local_file)){
			header('Cache-control: private');
			header('Content-Type: application/octet-stream');
			header('Content-Length: '.filesize($local_file));
			header('Content-Disposition: filename='.$download_file);

			flush();
			$file = fopen($local_file, "r");
			while(!feof($file)){
				if($download_rate)print fread($file, round($download_rate * 1024));
				else print fread($file);
				flush();
			  if($download_rate)sleep(1);
			};
			fclose($file);
		}else{
			die('Error: The file '.$local_file.' does not exist!');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URL
	 *
	 * Fetching link from another host and save it locally
	 *
	 * @access	public
	 * @param	string 	File or URL to fetch
	 * @param	string 	Where to save
	 * @param	boolean Type wither use copy or content getter (cg use more memory)
	 * @return	mixen
	 */
	public static function fetchUrl($url,$file,$type=false) {
		if($type){
			return copy($url, $file);
		}else{
			if(($s = file_get_contents($url)) === false)return false;
			return file_put_contents($filename, $s);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch URL
	 *
	 * Fetching link from another host and save it locally
	 *
	 * @access	public
	 * @param	string 	File or URL to fetch
	 * @param	string 	Where to save
	 * @param	boolean Type wither use copy or content getter (cg use more memory)
	 * @return	mixen
	 */
	public static function request($url) {
		if (extension_loaded('curl')) {
			$ch = curl_init() or die ( curl_error() );
			$timeout = 10;
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec( $ch );
			curl_close( $ch );

			if($data) {
				return (new SimpleXMLElement($data));
			}else{
				return false;
			}
		}else{
			if(($data = file_get_contents($url)) === false){
				return false;
			}
			return $data;
		}
	}
}

/* End of file File.php */
/* Location: ./system/core/File.php */
