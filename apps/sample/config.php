<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/*
* CONFIG File
*
* @package		codeHive
* @subpackage	Core
* @category	Libraries
* @author		Tamer Zorba
* @link		http://purecis.com/
*/

return array(
    'index' => 'index.php',
    // title for the app
    'title' => 'Sample App',

    // app, website, api, cli
    'type' => 'app',

    // plugins that the app will load
    'modules' => array(),

    /*
     * Define DataBase and Connection
     *
     * Acceptable Database Types (sqlite,mysql,pgsql,oracle,firebird,infomix,dblib,odbc)
     *
     */
    'database' => array(
        'type' => 'mysql',
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'purecis-v2',
        'user' => 'root',
        'pass' => 'root',
        'prefix' => '',
    ),

    /*
     * Define Upload
    */
    'upload' => array(
        'maxsize' => 32,
        'public' => 'JPG,JPEG,PNG,GIF,MP3,WAV,PDF,MP4',
        'private' => 'JPG,JPEG,PNG,GIF,MP3,M4A,OGG,WAV,MP4,M4V,MOV,WMV,AVI,MPG,OGV,3GP,3G2,PDF,ODT,DOC,DOCX,XLS,XLSX,PPT,PPTX,PPS,PPSX,RAR,ZIP,GZ,TAR',
        'thumbnails' => array(
            'xs' => 150,
            'sm' => 300,
            'md' => 600,
            'lg' => 1200,
        ),
    ),

    'license' => array(
        'key' => '56500ef9db9a04820cd29cd444cab675',
        'secret' => '56500ef9db9a04820cd29cd444cab675',
        'hash' => md5('0598251486'), //for passwords on this site
    ),

    'timezone' => 'Asia/Jerusalem',

    // development, production, trace
    'ENVIRONMENT' => 'trace',
);
