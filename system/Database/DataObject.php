<?php
/**
 * Data Object.
 * Database connection
 *
 * @category   codeHive Database
 * @package    System
 * @author     Tamer Zorba <tamer.zorba@purecis.com>
 * @copyright  Copyright (c) 2013 - 2016, Purecis, Inc.
 * @license    http://codehive.purecis.com/license  MIT License
 * @version    Release: 3.0
 * @link       http://codehive.purecis.com/package/System.DataObject
 * @since      Class available since Release 2.0.0
 */
namespace App\System;

use \PDO;
use \stdClass;

class DataObject
{
    /**
     * Database Connection.
     */
    private static $current = 'default';
    protected static $connections = [];

    // --------------------------------------------------------------------

    /**
     * Database Connect.
     *
     * @param	string 	Database name
     * @param	string 	Database host
     * @param	string 	Database user
     * @param	string 	Database pass
     * @param	string 	Database port
     *
     * @return mixen
     */
    public static function setDB(){
        $name = func_get_arg(0);
        $db = new Scope('config.database');

        if(in_array($name, $db->keys())){
            self::$current = $name;
            return true;
        }else{
            echo "ERR: Couldn't find connection called " . $name;
            return false;
        }
    }

    public static function connection()
    {
        $connection = &self::$connections[self::$current];
        if(isset($connection)){
            return $connection;
        }
        
        if(!self::setDB(self::$current)){
            return false;
        }
        
        $config = new Scope('config.database');
        $db = $config->get(self::$current);

        try {
            switch ($db->driver) {
                case 'sqlite':
                    $dsn = "sqlite:{$db->name}";
                    break;

                case 'pgsql':
                    $dsn = "pgsql:dbname={$db->name};host={$db->host}";
                    break;

                case 'oracle':
                    $dsn = "OCI:dbname={$db->name};charset=UTF-8";
                    break;

                case 'firebird':
                    $dsn = "firebird:dbname={$db->host}:{$db->name}";
                    $db->user = 'SYSDBA';
                    break;

                case 'infomix':
                    $dsn = "informix:DSN={$db->name}";
                    break;

                case 'dblib':
                    $dsn = "dblib:host={$db->host}:{$db->port};dbname={$db->name}";
                    break;

                case 'odbc':
                    $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq={$db->name}";
                    break;

                case 'pervasive':
                    $dsn = "Driver={Pervasive ODBC Client Interface};ServerName={$db->host};ServerDSN={$db->name};";
                    break;

                case 'mysql':
                default:
                    $db->port = $db->port ?: 3306;
                    $dsn = "mysql:host={$db->host};dbname={$db->name};port={$db->port};charset=utf8";
                    break;
            }

            $connection = new PDO($dsn, $db->user, $db->pass);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            $hive = new Scope('config.hive');
            if (strtolower($hive->environment) == 'debug') {
                // Debug::error('Database Error', String::escape($e->getMessage()));
                echo 'Database Error :' . $e->getMessage();
            } else {
                die('Database Error :'.$e->getMessage());
            }
        }

        return $connection;
    }

    // --------------------------------------------------------------------

    /**
     * Database Query.
     *
     * @param	string 	sql
     *
     * @return array
     */
    public static function query($sql, $args = array())
    {
        $connection = self::connection();
        $return = new stdClass();
        try {
            if (!sizeof($args)) {
                $query = $connection->query($sql);
            } else {
                $query = $connection->prepare($sql);
                $query->execute($args);
            }

            $type = strtoupper(substr($sql, 0, 10));
            if (strpos($type, 'INSERT') !== false) {
                $type = 'insert';
            } elseif (strpos($type, 'UPDATE') !== false) {
                $type = 'update';
            } elseif (strpos($type, 'DELETE') !== false) {
                $type = 'delete';
            } elseif (strpos($type, 'TRUNCATE') !== false) {
                $type = 'truncate';
            } else {
                $type = 'select';
            }

            if (in_array($type, array('select', 'update', 'delete', 'truncate'))) {
                $return->count = $query->rowCount();
            }
            if ($type == 'select') {
                $return->record = $query->fetchAll(PDO::FETCH_CLASS);
            }
            if ($type == 'insert') {
                $return->last = $connection->lastInsertId();
            }

            $return->status = true;

            // if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            //     Trace::count('Database Queries');
            // }
        } catch (PDOException $e) {
            // if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            //     Trace::error('Database Error', String::escape($e->getMessage()));
            // }
            $return->status = false;
            $return->error = $e->getMessage();
        }

        // if (in_array(strtoupper($config['ENVIRONMENT']), array('TRACE', 'DEVELOPMENT'))) {
        //     $return->sql = $sql;
        //     // TODO : append sql to tracer ..
        // }

        return $return;
    }

    public static function exec($sql)
    {
        $connection = self::connection();
        $object = new stdClass();
        
        try {
            $connection->exec($sql);
            $object->status = true;
            
            // if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            //     Trace::count('Database Queries');
            // }
        } catch(PDOException $e) {
            // if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            //     Trace::error('Database Error', String::escape($e->getMessage()));
            // }
            $object->status = false;
            $object->error = $e->getMessage();
        }

        // if (in_array(strtoupper($config['ENVIRONMENT']), array('TRACE', 'DEVELOPMENT'))) {
        //     $object->sql = $sql;
        //     // TODO : append sql to tracer ..
        // }

        return $object;
    }
}

/* End of file Database.php */
/* Location: ./system/core/Database.php */
