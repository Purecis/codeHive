<?php
namespace App\System;

class FileSystem
{
    /**
     * File Format
     *
     * Calculate file format from Byte to MB,GB,TB and PB
     *
     * @access	public
     * @param	integer File size in Bytes
     * @return	string
     */
    public static function format($size, $float=2)
    {
        if ($size == 0) {
            return "0 Byte";
        }
        $unit=array('Byte','KB','MB','GB','TB','PB');
        $n = round($size/pow(1024, ($i=floor(log($size, 1024)))), $float);
        if ($float) {
            $n = sprintf("%.{$float}f", $n);
        }
        return $n.' '.$unit[$i];
    }

    public static function rmdirRecursive($dir)
    {
        foreach (scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (is_dir($dir . "/" . $file)) {
                self::rmdirRecursive($dir . "/" . $file);
            } else {
                unlink($dir . "/" . $file);
            }
        }
        rmdir($dir);
    }

    public static function mkdirRecursive($dir){
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public static function unzip($file, $to){
        $zip = new \ZipArchive;
        if ($zip->open($file) === true) {
            $zip->extractTo($to);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    /**
	 * File Extension
	 * extract Extension from string
	 *
	 * @access	public
	 * @param	string Path to File
	 * @return	string
	 */
	public static function extension($filename){
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}
}
