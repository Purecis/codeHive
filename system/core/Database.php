<?php

defined('VERSION') or exit('Direct access to this location is not permitted');

/*
 * codeHive Database.
 *
 * Database class provide the framework module based infrastructure
 *
 * @category    core
 *
 * @author      Tamer Zorba <abo.al.tot@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, PureCore International Solutions (http://purecis.com/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 *
 * @link       http://codehive.purecis.com/package/Database
 * @since      File available since Release 2.0.0
 *
 * @version    V: 2.1.0
 */
class Database
{
    /**
     * Database Connection.
     */
    private static $current = null;
    protected static $connection = null;

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
    public static function connect($con = array())
    {
        global $config;

        if (!$config['database']) {
            $config['database'] = array();
        }

        if (self::$connection && (!isset($con['name']) or $con['name'] == self::$current)) {
            return self::$connection;
        }
        if (self::$connection && $con['name'] == $config['database']['name']) {
            return self::$connection;
        }

        $crd = new stdClass();
        $crd->type = isset($con['type']) ? $con['type'] : $config['database']['type'];
        $crd->host = isset($con['host']) ? $con['host'] : $config['database']['host'];
        $crd->name = isset($con['name']) ? $con['name'] : $config['database']['name'];
        $crd->user = isset($con['user']) ? $con['user'] : $config['database']['user'];
        $crd->pass = isset($con['pass']) ? $con['pass'] : $config['database']['pass'];
        $crd->port = isset($con['port']) ? $con['port'] : $config['database']['port'];

        try {
            switch ($crd->type) {
                case 'sqlite':
                    $dsn = "sqlite:{$crd->name}";
                    break;

                case 'pgsql':
                    $dsn = "pgsql:dbname={$crd->name};host={$crd->host}";
                    break;

                case 'oracle':
                    $dsn = "OCI:dbname={$crd->name};charset=UTF-8";
                    break;

                case 'firebird':
                    $dsn = "firebird:dbname={$crd->host}:{$crd->name}";
                    $crd->user = 'SYSDBA';
                    break;

                case 'infomix':
                    $dsn = "informix:DSN={$crd->name}";
                    break;

                case 'dblib':
                    $dsn = "dblib:host={$crd->host}:{$crd->port};dbname={$crd->name}";
                    break;

                case 'odbc':
                    $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq={$crd->name}";
                    break;

                case 'mysql':
                default:
                    $crd->port = $crd->port ?: 3306;
                    $dsn = "mysql:host={$crd->host};dbname={$crd->name};port={$crd->port};charset=utf8";
                    break;
            }

            self::$connection = self::$current = new PDO($dsn, $crd->user, $crd->pass);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::error('Database Error', String::escape($e->getMessage()));
            } else {
                die('Database Error :'.$e->getMessage());
            }
        }

        return self::$connection;
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
        global $config;

        self::connect();
        $return = new stdClass();
        try {
            if (!sizeof($args)) {
                $query = self::$connection->query($sql);
            } else {
                $query = self::$connection->prepare($sql);
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
                $type = 'select'; // show
            }

            $fetch = isset($config['database']['fetch']) && $config['database']['fetch'] == 'array' ? PDO::FETCH_ASSOC : PDO::FETCH_CLASS;
            if (in_array($type, array('select', 'update', 'delete', 'truncate'))) {
                $return->count = $query->rowCount();
            }
            if ($type == 'select') {
                $return->record = $query->fetchAll($fetch);
            }
            if ($type == 'insert') {
                $return->last = self::$connection->lastInsertId();
            }

            $return->status = true;

            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::count('Database Queries');
            }
        } catch (PDOException $e) {
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::error('Database Error', String::escape($e->getMessage()));
            }
            $return->status = false;
            $return->error = $e->getMessage();
        }

        if (in_array(strtoupper($config['ENVIRONMENT']), array('TRACE', 'DEVELOPMENT'))) {
            $return->sql = $sql;
            // TODO : append sql to tracer ..
        }

        return $return;
    }

    public static function exec($sql)
    {
        global $config;
        self::connect();
        $object = new stdClass();
        
        try {
            self::$connection->exec($sql);
            $object->status = true;
            
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::count('Database Queries');
            }
        } catch(PDOException $e) {
            if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
                Trace::error('Database Error', String::escape($e->getMessage()));
            }
            $object->status = false;
            $object->error = $e->getMessage();
        }

        if (in_array(strtoupper($config['ENVIRONMENT']), array('TRACE', 'DEVELOPMENT'))) {
            $object->sql = $sql;
            // TODO : append sql to tracer ..
        }

        return $object;
    }
}

/* End of file Database.php */
/* Location: ./system/core/Database.php */
