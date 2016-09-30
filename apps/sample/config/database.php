<?php
/**
 * database config
 *
 * @category   codeHive System
 * @package    Config
 * @author     Tamer Zorba <tamer.zorba@purecis.com>
 * @copyright  Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license    http://codehive.purecis.com/license  MIT License
 * @version    Release: 3.0
 * @link       http://codehive.purecis.com/docs/database
 * @since      Class available since Release 3.0
 */

return array(
    'type' => 'mysql',
    'host' => 'localhost',
    'port' => '3306',
    'name' => 'codeHive',
    'user' => 'root',
    'pass' => 'root',
    'prefix' => '',
    'essential' => true,
    "extended" => true // this will add meta table and other stuff to concern about
);
