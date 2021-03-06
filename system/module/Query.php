<?php
 if (!defined('VERSION')) {
     exit('Direct access to this location is not permitted.');
 }
/**
 * Purecis Query Module.
 *
 * control URL Parameters
 *
 * @category	Libraries
 *
 * @author		Tamer Zorba
 *
 * @link		http://purecis.com/
 */
class Query
{
    /**
     * List of arrays.
     */
    protected static $callback = array();

    // --------------------------------------------------------------------

    /**
     * Query install.
     *
     * @return string
     */
    public static function __install()
    {
        // check sql install
    }

    // --------------------------------------------------------------------

    /**
     * Query structure.
     *
     * @return string
     */
    public static function __structure($table = false)
    {
        //ENGINE=InnoDB DEFAULT CHARSET=utf8;

        $st = array(
            'users' => [
                'id' => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'oauth' => "varchar(50) NOT NULL DEFAULT '0'",
                'name' => 'varchar(200) NOT NULL',
                'login' => 'varchar(200) NOT NULL',
                'email' => 'varchar(100) NOT NULL',
                'pass' => 'varchar(200) NOT NULL',
                'group' => "int(2) NOT NULL DEFAULT '0'",
                'status' => "varchar(20) NOT NULL DEFAULT 'new'",
                'rules' => 'text',
                'rel' => 'varchar(50) DEFAULT NULL',
            ],
            'terms' => [
                'id' => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'name' => 'varchar(200) NOT NULL',
                'taxonomy' => 'varchar(200) NOT NULL',
                'parent' => "int(11) NOT NULL DEFAULT '0'",
                'count' => "int(11) NOT NULL DEFAULT '0'",
            ],
            'relations' => [
                'id' => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'oid' => 'bigint(20) NOT NULL',
                'rid' => 'bigint(20) NOT NULL',
                'table' => 'varchar(50) NOT NULL',
                'taxonomy' => 'varchar(50)',
            ],
            'objects' => [
                'id' => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'author' => 'bigint(20) NOT NULL',
                'status' => "varchar(20) NOT NULL DEFAULT ''",
                'permalink' => "varchar(250) NOT NULL DEFAULT ''",
                'parent' => 'bigint(20) NOT NULL DEFAULT 0',
                'time' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
                'taxonomy' => "varchar(50) NOT NULL DEFAULT ''",
                'rel' => "varchar(50) NOT NULL DEFAULT ''",
            ],
            'library' => [
                'id' => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'author' => 'bigint(20) NOT NULL',
                'name' => "varchar(200) NOT NULL DEFAULT ''",
                'path' => "text NOT NULL DEFAULT ''",
                'updated' => 'int(11)',
                'extension' => 'varchar(20) NOT NULL DEFAULT 0',
                'size' => 'varchar(50) NOT NULL DEFAULT 0',
                'tags' => 'tinytext',
            ],
        );
        if ($table) {
            if (isset($st[$table])) {
                return $st[$table];
            } else {
                return array();
            }
        }

        return $st;
    }

    // --------------------------------------------------------------------

    public static function inStructure($col, $table)
    {
        global $config;
        if (isset($config['database']['essential'])) {
            if ($config['database']['essential'] == true) {
                return true;
            }
        }
        $st = self::__structure($table);

        return array_key_exists($col, $st);
    }
    // --------------------------------------------------------------------

    /**
     * Query execute.
     *
     * @param	array 	sql array
     */
    public static function execute($arr)
    {
        // todo
        // terms and library with better join like
        // (:term:termname, :library:libname => "data2get,As,Arr,internalMeta")

        global $config;

        $sql = array();
        if (!empty($config['database']['prefix']) && $arr['table'] != 'meta') {
            $arr['table'] = $config['database']['prefix'].$arr['table'];
        }
        //type, table, data, where, whereSeperator, order, limit
        if (!isset($arr['type'])) {
            $arr['type'] = 'select';
        }

        $table = $arr['table'];

        if ($arr['type'] == 'select') {
            // start parsing meta table
            if (isset($arr['data'])) {
                if (!is_array($arr['data'])) {
                    $arr['data'] = explode(',', $arr['data']);
                }
                if (!isset($arr['join'])) {
                    $arr['join'] = array();
                }
                foreach ($arr['data'] as $k => $v) {
                    //key
                    /*
                    if(!self::inStructure($k, $table)){
                        if(!strpos($k, ".")){
                            $arr['join']["meta as {$k}"] = "{$k}.oid = {$table}.id and {$k}.key='{$k}' and {$k}.table = '{$table}'";
                            unset($arr['data'][$k]);
                            $k = "{$k}.value";
                            $arr['data'][$k] = $v;
                        }else{

                        }
                    }*/
                    //value on select only
                    if ($arr['type'] == 'select') {
                        if (!self::inStructure($v, $table)) {
                            $v = trim($v);
                            //echo "{$v} not exist<hr>";
                            $arr['join']["meta as `{$v}`"] = "`{$v}`.`oid` = `{$table}`.`id` and `{$v}`.`key`='{$v}' and `{$v}`.`table` = '{$table}'";
                            $arr['data'][$k] = "`{$v}`.`value` as `{$v}`";
                            //unset($arr['data'][$k]);
                            // fix where here ::value::
                        } else {
                            if (!strpos($v, '.')) {
                                if (strpos($arr['data'][$k], '~') === false) {
                                    $arr['data'][$k] = "`{$table}`.`{$arr['data'][$k]}`";
                                }
                            }
                            $arr['data'][$k] = str_replace('~', '', $arr['data'][$k]);
                        }
                    }
                }
            }
            // checking library here
            if (isset($arr['library1'])) {
                array_push($arr['data'], 'librarytable.id as libId');
                array_push($arr['data'], 'librarytable.name as libName');
                array_push($arr['data'], 'librarytable.path as libPath');
                if (!isset($arr['join'])) {
                    $arr['join'] = array();
                }
                $arr['join']['relations as libraryrelations'] = "{$table}.id = libraryrelations.oid";
                $arr['join']['library as librarytable'] = 'libraryrelations.rid = librarytable.id';
                $arr['groupby'] = "`{$table}`.`id`";
            }
            // checking library here
            if (isset($arr['library'])) {
                if (!isset($arr['join'])) {
                    $arr['join'] = array();
                }
                $arr['join']['relations as libraryrelations'] = "{$table}.id = libraryrelations.oid";
                $arr['join']['library as librarytable'] = 'libraryrelations.rid = librarytable.id';
                $arr['groupby'] = "`{$table}`.`id`";

            //	array_push($arr['data'], 'librarytable.id as libId');
            //	array_push($arr['data'], 'librarytable.name as libName');
            //	array_push($arr['data'], 'librarytable.path as libPath');

                foreach ($arr['library'] as $library) {
                    if (!self::inStructure($library, 'library')) {
                        //	array_push($arr['data'], "thisusermeta.value as author_{$author}");
                    //	$arr['join']["meta as `thisusermeta`"] = "`thisusermeta`.`oid` = `{$table}`.`author` and `thisusermeta`.`key`='{$author}' and `thisusermeta`.`table` = 'users'";
                    } else {
                        array_push($arr['data'], "librarytable.{$library} as library_{$library}");
                    }
                }
            }
            // checking user here
            if (isset($arr['author'])) {
                if (!isset($arr['join'])) {
                    $arr['join'] = array();
                }
                $arr['join']['users as thisuser'] = "{$table}.author = thisuser.id";

                foreach ($arr['author'] as $author) {
                    if (!self::inStructure($author, 'users')) {
                        array_push($arr['data'], "thisusermeta.value as author_{$author}");
                        $arr['join']['meta as `thisusermeta`'] = "`thisusermeta`.`oid` = `{$table}`.`author` and `thisusermeta`.`key`='{$author}' and `thisusermeta`.`table` = 'users'";
                    } else {
                        array_push($arr['data'], "thisuser.{$author} as author_{$author}");
                    }
                }
            }
        }
        if (isset($arr['where'])) {
            if (is_array($arr['where'])) {
                if (!isset($arr['join'])) {
                    $arr['join'] = array();
                }
                foreach ($arr['where'] as $k => $v) {
                    //fix value
                    // if array then put or between them
                    if (is_array($v)) {
                        continue;
                    }
                    if (strpos($v, '~') === 0) {
                        $v = str_replace('~', '', $v);
                        if (!strpos($v, '.')) {
                            if (!self::inStructure($v, $table)) {
                                $arr['join']["meta as `{$k}`"] = "`{$k}`.`oid` = `{$table}`.`id` and `{$k}`.`key`='{$k}' and `{$k}`.`table` = '{$table}'";
                                $v = "`{$k}`.`{$v}`";
                            } else {
                                $v = "`{$table}`.`{$v}`";
                            }
                        } else {
                            $v = str_replace('.', '`.`', $v);
                            $v = "`{$v}`";
                        }
                    }

                    //fix key
                    if (!self::inStructure($k, $table)) {
                        $k = trim($k);
                        if (!strpos($k, '.')) {
                            $arr['join']["meta as `{$k}`"] = "`{$k}`.`oid` = `{$table}`.`id` and `{$k}`.`key`='{$k}' and `{$k}`.`table` = '{$table}'";
                            unset($arr['where'][$k]);
                            $arr['where']["{$k}.value"] = $v;
                        }
                    } else {
                        if (!strpos($k, '.')) {
                            unset($arr['where'][$k]);
                            $arr['where']["{$table}.{$k}"] = $v;
                        }
                    }
                }
            }
        }

        switch ($arr['type']) {
            case 'sql':

                # code...
                break;
            case 'insert':
                array_push($sql, 'insert');
                if (isset($arr['ignore']) && $arr['ignore'] == true) {
                    array_push($sql, 'IGNORE');
                }
                array_push($sql, 'into');
                array_push($sql, $arr['table']);
                $keys = array();
                $values = array();
                foreach ($arr['data'] as $k => $v) {
                    if (self::inStructure($k, $table)) {
                        array_push($keys, "`$k`");
                        $v = string::escape($v);
                        array_push($values, "'{$v}'");
                    }
                }
                array_push($sql, '(');
                array_push($sql, implode(', ', $keys));
                array_push($sql, ') values (');
                array_push($sql, implode(', ', $values));// fix for multible
                array_push($sql, ')');
                break;

            case 'delete':
                array_push($sql, 'delete from');
                array_push($sql, $arr['table']);
                # code...
                break;

            case 'update':
                array_push($sql, 'update');
                array_push($sql, $arr['table']);

                if (isset($arr['join'])) {
                    $j = array();
                    foreach ($arr['join'] as $tbl => $join) {
                        array_push($j, "LEFT JOIN {$tbl} ON {$join}");
                    }
                    array_push($sql, implode($j, ' '));
                }

                array_push($sql, 'set');
                $values = array();
                foreach ($arr['data'] as $k => $v) {
                    if (self::inStructure($k, $table)) {
                        $v = string::escape($v);
                        array_push($values, self::parse_eq("{$table}.{$k}", $v, $table));
                        //array_push($values,"`{$table}`.`{$k}` = '{$v}'");
                    }
                }
                array_push($sql, implode(', ', $values));
                break;

            default://select

                # code...
                array_push($sql, 'select');
                if (isset($arr['data'])) {
                    if (is_array($arr['data'])) {
                        $arr['data'] = implode(', ', $arr['data']);
                    }
                    array_push($sql, $arr['data']);
                } else {
                    array_push($sql, '*');
                }

                array_push($sql, 'from');
                array_push($sql, $arr['table']);
                break;
        }
        if (isset($arr['join']) && $arr['type'] != 'update') {
            $j = array();
            foreach ($arr['join'] as $tbl => $join) {
                array_push($j, "LEFT JOIN {$tbl} ON {$join}");
            }
            array_push($sql, implode($j, ' '));
        }
        if (isset($arr['where'])) {
            if (is_array($arr['where'])) {
                if (sizeof($arr['where']) > 0) {
                    $wh = array();
                    array_push($sql, 'where');
                    foreach ($arr['where'] as $key => $value) {
                        array_push($wh, self::parse_eq($key, $value, $table));
                    }
                    $seperate = isset($arr['whereSeperator']) ? $arr['whereSeperator'] : 'and';
                    $where = implode($wh, " {$seperate} ");
                    array_push($sql, $where);
                }
            } elseif (is_string($arr['where'])) {
                array_push($sql, 'where');
                array_push($sql, $arr['where']);
            }
        }
        if (isset($arr['groupby'])) {
            array_push($sql, "GROUP BY {$arr['groupby']}");
        }
        if (isset($arr['order'])) {
            array_push($sql, 'order by');
            $or = $arr['order'];
            if (is_object($or)) {
                $a = (array) $or;
                if (!isset($config['database']['essential'])) {
                    $col = 'CAST('.key($or).' AS SIGNED)';
                } else {
                    $col = $or;
                }
                array_push($sql, $col.' '.$a[key($or)]);
            } elseif (is_array($or)) {
                $a = array();
                foreach ($or as $key => $value) {
                    if (!isset($config['database']['essential'])) {
                        if (strpos($key, '(') === false) {
                            $key = 'CAST('.$key.' AS SIGNED)';
                        }
                    }
                    array_push($a, $key.' '.$value);
                }
                array_push($sql, implode($a, ', '));
            } else {
                if (strpos($or, '(')) {
                    array_push($sql, "{$or}");
                } else {
                    array_push($sql, "id {$or}");
                }
            }
        }
        if (isset($arr['limit'])) {
            $limit = 'limit '.$arr['limit'];
            array_push($sql, $limit);
        } else {
            if ($arr['type'] == 'select') {
                $limit = 'limit 1000';
                array_push($sql, $limit);
            }
        }

        $sql = implode($sql, ' ').';';

        // Extra SQL
        if(!isset($config['database']['essential']) || !$config['database']['essential']){
        if ($arr['type'] == 'insert') {
            $values = array();
            foreach ($arr['data'] as $k => $v) {
                if (!self::inStructure($k, $table) && !empty($v)) {
                    $v = string::escape($v);
                    array_push($values, "(LAST_INSERT_ID(), '{$k}', '{$v}','{$arr['table']}')");
                }
            }
            if (sizeof($values) > 0) {
                $extra = 'INSERT INTO meta (`oid`,`key`,`value`,`table`) VALUES ';
                $extra .= implode(', ', $values);
                $extra .= 'ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);';
                $sql .= $extra;
            }
        } elseif ($arr['type'] == 'update') {
            //set select where if where in some meta then update by id's (more than 1)
            // in upload not important if the value empty it will update any way
            $data_keys = array_keys($arr['data']);
            $meta_size = 0;
            $schema_size = 0;
            foreach ($data_keys as $ks) {
                if (!self::inStructure($ks, $table)) {
                    ++$meta_size;
                } else {
                    ++$schema_size;
                }
            }

            if ($meta_size > 0) {
                // to get id's
                $sid_q = self::execute(array(
                    'table' => $arr['table'],
                    'data' => array('id'),
                    'where' => isset($arr['where']) ? $arr['where'] : null,
                    'join' => isset($arr['join']) ? $arr['join'] : null,
                ));
                //all
                $sid = array();
                foreach ($sid_q->record as $d) {
                    array_push($sid, $d->id);
                }
            }

            if (isset($sid)) {
                $values = array();
                foreach ($arr['data'] as $k => $v) {
                    if (!self::inStructure($k, $table)) {
                        $v = string::escape($v);
                        foreach ($sid as $sid_k) {
                            array_push($values, "({$sid_k}, '{$k}', '{$v}','{$arr['table']}')");
                        }
                    }
                }
                $extra = 'INSERT INTO meta (`oid`,`key`,`value`,`table`) VALUES ';
                $extra .= implode(', ', $values);
                $extra .= 'ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);';

                if ($schema_size > 0) {
                    $sql .= $extra;
                } else {
                    $sql = $extra;
                }
            }
        } elseif ($arr['type'] == 'delete') {
            if (!isset($where)) {
                $where = '';
            }
            if (!isset($limit)) {
                $limit = '';
            }
            //print_r($limit);
            $idsQ = Database::query("select id from {$table} where {$where} {$limit};");// this to get ids for meta
            if ($idsQ->status) {
                $ids = array();
                foreach ($idsQ->record as $row) {
                    $row = (array) $row;
                    array_push($ids, $row['id']);
                }// todo : need optimization
            } else {
                $ids = array();
            }
            //print_r($ids);
            $ids = implode(',', $ids);
            // delete more than 1
            if (in_array($table, ['users', 'terms', 'objects', 'library'])) { // todo : create group definer function
                $sql .= "DELETE FROM meta WHERE `oid` in ({$ids})  AND `table` = '{$table}';";
            }
            if (in_array($table, ['terms', 'library'])) { // todo : create group definer function
                $sql .= "DELETE FROM relations WHERE `rid` in ({$ids})  AND `table` = '{$table}';";
            }
            if (in_array($table, ['object'])) {
                $sql .= "DELETE FROM relations WHERE `oid` in ({$ids});";
            }
            // delete from meta where `oid` = OLD.id  and `table` = 'terms'; // as trigger
        }
        } // end of extra sql
        //echo $sql;

        //return array();
        return Database::query($sql);
    }

    private static function parse_eq($key, $value, $table)
    {
        $key = str_replace('.', '`.`', $key);

        if (strpos($key, '(') !== false) {
            $key = str_replace($table, '', $key);
            $key = str_replace('`.`', '', $key);
            $key = str_replace('~', '', $key);
            //echo $key;
        }

        if (!is_array($value) && strpos($value, '~') === 0) {
            $value = str_replace('~', '', $value);
            if (!strpos($value, '.')) {
                if (!self::inStructure($value, $table)) {
                    $arr['join']["meta as `{$key}`"] = "`{$key}`.`oid` = `{$table}`.`id` and `{$key}`.`key`='{$key}' and `{$key}`.`table` = '{$table}'";
                    $value = "`{$key}`.`{$value}`";
                } else {
                    $value = "`{$table}`.`{$value}`";
                }
            } else {
                $value = str_replace('.', '`.`', $value);
                $value = "`{$value}`";
            }
            $normal_v = false;
        } else {
            $normal_v = true;
            if (is_array($value)) {
                if (!self::inStructure($key, $table)) {
                    $key = $key.'`.`value';
                } else {
                    $key = "{$table}`.`{$key}";
                }
                $value = ':in:'.implode(', ', $value);
            }
        }

        if (strpos($value, ':like:') !== false) {
            $value = str_replace(':like:', '', $value);
            if ($normal_v) {
                $value = "'{$value}'";
            }

            return "`{$key}` like {$value}";
        } elseif (strpos($value, ':is:') !== false) {
            $value = str_replace(':is:', '', $value);
            if ($normal_v) {
                $value = "'{$value}'";
            }

            return "`{$key}` is {$value}";
        } elseif (strpos($value, ':not:') !== false) {
            $value = str_replace(':not:', '', $value);
            if ($normal_v) {
                $value = "'{$value}'";
            }

            return "`{$key}` != {$value}";
        } elseif (strpos($value, ':inset:') !== false) {
            $value = stripslashes($value);
            $value = str_replace(':inset:', '', $value);

            $separator = 'or';
            if (strpos($value, 'or:') !== false) {
                $value = str_replace('or:', '', $value);
            }
            if (strpos($value, 'and:') !== false) {
                $separator = 'and';
                $value = str_replace('and:', '', $value);
            }
            $ex = explode(',', $value);
            $temp = array();
            foreach ($ex as $v) {
                array_push($temp, "FIND_IN_SET ({$v},`{$key}`)");
            }

            return '('.implode(" {$separator} ", $temp).')';
        } elseif (strpos($value, ':in:') !== false) {
            $value = stripslashes($value);
            $value = str_replace(':in:', '', $value);

            return "`{$key}` in ({$value})";
        } elseif (strpos($value, ':notin:') !== false) {
            $value = str_replace(':notin:', '', $value);

            return "`{$key}` not in ({$value})";
        } elseif (strpos($value, ':gt:') !== false) {
            $value = str_replace(':gt:', '', $value);

            return "`{$key}` > {$value}";
        } elseif (strpos($value, ':gte:') !== false) {
            $value = str_replace(':gte:', '', $value);

            return "`{$key}` >= {$value}";
        } elseif (strpos($value, ':lt:') !== false) {
            $value = str_replace(':lt:', '', $value);

            return "`{$key}` < {$value}";
        } elseif (strpos($value, ':lte:') !== false) {
            $value = str_replace(':lte:', '', $value);

            return "`{$key}` <= {$value}";
        } elseif (strpos($value, ':bet:') !== false) {
            $value = str_replace(':bet:', '', $value);
            $value = str_replace(',', ' and ', $value);

            return "(`{$key}` BETWEEN {$value})";
        } elseif (strpos($value, ':append:') !== false) {
            $value = str_replace(':append:', '', $value);

            return "`{$key}` = `{$key}` + {$value}";
        } else {
            if ($normal_v) {
                $value = "'{$value}'";
            }
            if (strpos($key, '(') !== false) {
                return "{$key} = {$value}";
            } else {
                return "`{$key}` = {$value}";
            }
        }
        // in set and things
    }

    // --------------------------------------------------------------------

    /**
     * Query get.
     *
     * Fetching Data from table
     *
     * @param	array 	sql array
     */
    public static function get($table, $arr = array())
    {
        /*
            select users.*
            ,birthday.value as birthday
            from users
            left join meta as birthday on birthday.oid = users.id and birthday.key='birthday' and birthday.table = 'users'
            ;
            */
        $arr['table'] = $table;
        $arr['type'] = 'select';
        // fix structure
        if (isset($arr['library'])) {
            //"LEFT JOIN {$tbl} ON {$join}"
            //"LEFT JOIN library ON {$join}"
            /*
            array_push($arr['data'], "library.id as libid");
            array_push($arr['data'], "library.name as libname");
            $arr['join'] = array(
                "relations"=>"objects.id = relations.oid",
                "library"=>"relations.rid = library.id"
            );
            */
            //unset($arr['library']);
        }

        return self::execute($arr);
    }

    // --------------------------------------------------------------------

    /**
     * Query set.
     *
     * Insert or Update Database table based on where is set
     *
     * @param	array 	sql array
     */
    public static function set($table, $arr = array())
    {
        $arr['table'] = $table;
        $arr['type'] = 'insert';
        if (isset($arr['where'])) {
            $arr['type'] = 'update';
        }

        return self::execute($arr);
    }

    // --------------------------------------------------------------------

    /**
     * Query remove.
     *
     * Delete rows from Table
     *
     * @param	array 	sql array
     */
    public static function remove($table, $arr = array())
    {
        $arr['table'] = $table;
        $arr['type'] = 'delete';

        return self::execute($arr);
    }

    // --------------------------------------------------------------------

    /**
     * Query on.
     *
     * @param	array 	sql array
     */
    public static function on($table)
    {
        Module::import('QueryBuilder');
        $bldr = new QueryBuilder();

        return $bldr->table($table);
    }
    public static function table($table){
        return self::on($table);
    }
}

/* End of file Query.php */
/* Location: ./system/module/Query.php */
