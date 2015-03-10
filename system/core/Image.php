<?php  if (!defined('VERSION'))exit('Direct access to this location is not permitted.');
/**
 * Purecis Module Class
 *
 * This class has image parsing controle 
 *
 * @package		codeHive
 * @subpackage	Core
 * @category	Libraries
 * @author		Tamer Zorba
 * @link		http://purecis.com/
 */

class Image{
	// todo .. image face detection and color optimization ( Brite, contrast )

	/**
		createThumb
	*/
	public static function resize($arr){
		$cls = new stdClass();

		$source = $arr['source'];
		$target = isset($arr['target'])?$arr['target']:null;

		if(!is_array($arr['size']))$arr['size'] = array($arr['size']);

		$thumb_width = isset($arr['size'][0])?$arr['size'][0]:150;
		$thumb_height = isset($arr['size'][1])?$arr['size'][1]:null;

		list($width_orig, $height_orig, $image_type) = getimagesize($source);
		if($thumb_width >= $width_orig && !isset($arr['force'])){
			$cls->target = $source;
			return $cls;
			//if($target){
			//	copy($source, $target);
			//	return;
			//}else{
				$thumb_width = $width_orig;
				$thumb_height = $height_orig;
			//}
		}

    	switch ($image_type){
   			case 1: $im = imagecreatefromgif($source); break;
			case 2: $im = imagecreatefromjpeg($source);  break;
			case 3: $im = imagecreatefrompng($source); break;
			default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
		}
		if(!isset($thumb_height)){
			$aspect_ratio = (float) $height_orig / $width_orig;
			$thumb_height = round($thumb_width * $aspect_ratio);
		}
		$newImg = imagecreatetruecolor($thumb_width, $thumb_height);
		$white = imagecolorallocate($newImg, 255, 255, 255);
		imagefill($newImg, 0, 0, $white);

		imagecopyresampled($newImg, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);
		if(isset($target)){
			imagejpeg($newImg,$target);
			$cls->target = $target;
			return $cls;
		}else{
			header( 'Content-Type: image/jpeg' );
			imagejpeg($newImg);
		}
	}

	/**
		watermark
	*/
	public static function watermark($watermark='logo.png',$srcImage='',$target=false){
		//if(!$target)$target = $srcImage;

		$stamp = imagecreatefrompng($watermark);
		$im = imagecreatefromjpeg($srcImage);

		$marge_right = 10;
		$marge_bottom = 10;
		$sx = imagesx($stamp);
		$sy = imagesy($stamp);

		imagecopy($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));

		if(isset($target)){
			imagejpeg($im,$target);
		}else{
			header( 'Content-Type: image/jpeg' );
			imagejpeg($im);
		}
		imagedestroy($im);
	}


}

?>