<?php
namespace App\System;


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
        if($size == 0)return "0 Byte";
	    $unit=array('Byte','KB','MB','GB','TB','PB');
		$n = round($size/pow(1024,($i=floor(log($size,1024)))),$float);
		if($float){
			$n = sprintf("%.{$float}f", $n);
		}
	    return $n.' '.$unit[$i];
	}

    public static function rmdirRecursive($dir) {
        foreach(scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir($dir . "/" . $file)) self::rmdirRecursive($dir . "/" . $file);
            else unlink($dir . "/" . $file);
        }
        rmdir($dir);
    }

}