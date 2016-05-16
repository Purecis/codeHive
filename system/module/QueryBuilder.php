<?php

if (!defined('VERSION')) {
    exit('Direct access to this location is not permitted.');
}

/**
 * Purecis QueryBuilder Module.
 *
 * This class Control QueryBuilder Requests
 *
 * @category        Libraries
 *
 * @author          Tamer Zorba
 *
 * @link            http://purecis.com/
 *
 * @todo            shared lock, lock for update
 */
class QueryBuilder
{
    /**
     * Variables.
     *
     * @var mixen
     */
    private $table;
    public $param = array();
    public $where = '';
    private $join = '';
    private $group = array();
    private $order = array();
    private $limit = '';
    private $offset = '';
    private $query;
    private $records;
    private $union = '';
    public $join_on = '';
    private $with = array();

    // --------------------------------------------------------------------
    public function __get($property)
    {
        // echo "Getting '$property'\n";

        // return 123;
    }
    /**
     * ajax table.
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * set params to query.
     *
     * @param string $args Mix of data to get
     *
     * @return object QueryBuilder
     */
    public function param()
    {
        $this->param = array_merge($this->param, func_get_args());

        return $this;
    }

    public function where()
    {
        if (strlen($this->where)) {
            $this->where .= ' AND ';
        }

        $args = func_get_args();
        $this->where .= $this->_whereParse($args);

        return $this;
    }

    public function orWhere()
    {
        if (strlen($this->where)) {
            $this->where .= ' OR ';
        }

        $args = func_get_args();
        $this->where .= $this->_whereParse($args);

        return $this;
    }

    /**
     * Builder : join.
     *
     * @return object QueryBuilder
     */
    public function join()
    {
        $args = func_get_args();
        $this->join .= $this->_joinParse($args);

        return $this;
    }
    public function leftJoin()
    {
        $args = func_get_args();
        $this->join .= $this->_joinParse($args, "LEFT");

        return $this;
    }
    public function rightJoin()
    {
        $args = func_get_args();
        $this->join .= $this->_joinParse($args, "RIGHT");

        return $this;
    }
    public function outerJoin()
    {
        // TODO : support right outer and left outer
        $args = func_get_args();
        $this->join .= $this->_joinParse($args, "FULL OUTER");

        return $this;
    }
    public function innerJoin()
    {
        $args = func_get_args();
        $this->join .= $this->_joinParse($args, "INNER");

        return $this;
    }

    public function on()
    {
        $args = func_get_args();
        if (!strlen($this->join_on)) {
            $str = ' ON ';
        } else {
            $str = ' AND ';
        }

        $str .= call_user_func_array(array($this, '_parseOperators'), $args);

        $this->join_on .= $str;

        return $this;
    }

    public function orOn()
    {
        $args = func_get_args();
        if (!strlen($this->join_on)) {
            $str = ' ON ';
        } else {
            $str = ' OR ';
        }

        $str .= call_user_func_array(array($this, '_parseOperators'), $args);

        $this->join_on .= $str;

        return $this;
    }

    /**
     * Builder : group.
     *
     * @return object QueryBuilder
     */
    public function group()
    {
        $this->group = array_merge($this->group, func_get_args());

        return $this;
    }

    /**
     * Builder : order.
     *
     * @return object QueryBuilder
     */
    public function order()
    {
        $args = func_get_args();
        if (sizeof($args) == 2) {
            array_push($this->order, array($args[0], strtoupper($args[1])));
        } elseif (sizeof($args) == 1) {
            array_push($this->order, [$args[0], 'DESC']);
        } else {
            array_push($this->order, ['id', 'DESC']);
        }

        return $this;
    }

    public function limit()
    {
        $args = func_get_args();
        if (!is_numeric($args[0]) || (isset($args[1]) && !is_numeric($args[1]))) {
            return $this;
        }

        if (sizeof($args) == 2) {
            $this->limit = " LIMIT {$args[0]}, {$args[1]}";
        } else {
            $this->limit = " LIMIT {$args[0]}";
        }

        return $this;
    }

    public function offset()
    {
        $args = func_get_args();
        if (!is_numeric($args[0])) {
            return $this;
        }

        if (empty($this->limit)) {
            $this->limit = ' LIMIT 10';
        }
        $this->offset = " OFFSET {$args[0]}";

        return $this;
    }

    public function union()
    {
        $args = func_get_args();
        $this->union .= $this->_unionParse($args[0]);

        return $this;
    }

    public function unionAll()
    {
        $args = func_get_args();
        $this->union .= $this->_unionParse($args[0], 'ALL ');

        return $this;
    }

    public function with()
    {
        $args = func_get_args();

        array_push($this->with, $args);

        return $this;
    }
    public function appearAs(){
        $args = func_get_args();
        $this->appear = $args[0];
        return $this;
    }
    public function relation()
    {
        $args = func_get_args();
        $col = isset($args[2]) ? $args[2] : 'id';
        $this->withFirstColumn = isset($this->withFirstColumn) ? $this->withFirstColumn : $col;
        $this->withSecColumn = $args[0];

        // TODO: Save Seperator `=`, `in`, `not in`, `!=`
        if (!isset($args[1]) || (isset($args[1]) && $args[1] == '=')) {
            $this->masterQuery->param = [];
            array_push($this->masterQuery->param, $col);
            $this->where($args[0], 'in', $this->masterQuery);
        }

        return $this;
    }
    private function withParse()
    {
        foreach ($this->with as $args) {
            $table = $args[0]; // if is string only

            $q = new self();
            $q->table($table);
            $q->appear = $args[0];

            $q->masterQuery = clone $this;

            if (is_callable($args[1]) && $args[1] instanceof Closure) {
                call_user_func($args[1], $q);
                if (array_search($q->withSecColumn, $q->param) === false && sizeof($q->param)) {
                    array_push($q->param, $q->withSecColumn);
                }
            } else {
                array_shift($args);
                call_user_func_array(array($q, 'relation'), $args);
            }

            $newQuery = $q->get();

            if($newQuery->status){
                foreach ($this->records->record as $record) {
                    $record->{$q->appear} = array_values(array_filter(
                    $newQuery->record,
                    function ($e) use ($q, $record) {
                        // TODO : support `in` method
                        // TODO : shift name if send with dot
                        return $e->{$q->withSecColumn} == $record->{$q->withFirstColumn};
                    }));
                    // TODO: remove the sec column from the child if not sended
                }
            }else{
                if (isset($this->records->error) && is_string($this->records->error)) {
                    $temp = $this->records->error;
                    $this->records->error = [];
                    array_push($this->records->error, $temp);
                }else{
                    $this->records->error = [];
                }
                array_push($this->records->error, $newQuery->error);
            }

            if (is_string($this->records->sql)) {
                $temp = $this->records->sql;
                $this->records->sql = [];
                array_push($this->records->sql, $temp);
            }
            array_push($this->records->sql, $newQuery->sql);
        }

        return $this;
    }

    /**
     * generate SELECT statement.
     *
     * @return object SQL Query
     */
    public function _get()
    {
        $args = func_get_args();

        $this->query = 'SELECT ';
        $this->query .= $this->_param();
        $this->query .= ' FROM ';
        $this->query .= $this->_table($this->table);
        $this->query .= $this->join;
        $this->query .= $this->_where();
        $this->query .= $this->_group();
        $this->query .= $this->_order();
        $this->query .= $this->limit;
        $this->query .= $this->offset;
        $this->query .= $this->union;
        if (!isset($args[0])) {
            $this->query .= ';';
        }

        return $this->query;
    }

    public function get()
    {
        $this->_get();
        $this->records = Database::query($this->query);
        $this->withParse();

        return $this->records;
    }

    /**
     * generate INSERT, UPDATE statement.
     *
     * @return object SQL Query
     */
    private function _set()
    {
        $args = func_get_args();

        if (!strlen($this->where)) {
            $this->query = 'INSERT INTO ';
            $this->query .= $this->_table($this->table);
            $this->query .= ' ('.$this->_param('insert', 1).')';
            $this->query .= ' VALUES';
            $this->query .= ' ('.$this->_param('insert', 2).')';
        } else {
            $this->query = 'UPDATE ';
            $this->query .= $this->_table($this->table);
            $this->query .= ' SET ';
            $this->query .= $this->_param('update');
            $this->query .= $this->join;
            $this->query .= $this->_where();
        }

        $this->query .= ';';

        return $this->query;
    }

    public function set()
    {
        $this->_set();
        // return $this->query;
        return $this->records = Database::query($this->query);
    }

    /**
     * generate DELETE statement.
     *
     * @return object SQL Query
     */
    private function _remove()
    {
        $this->query = 'DELETE FROM ';
        $this->query .= $this->_table($this->table);
        $this->query .= $this->_where();
        $this->query .= $this->_order();
        $this->query .= $this->limit;
        $this->query .= ';';

        return $this->query;
    }

    public function remove()
    {
        $this->_remove();

        return $this->records = Database::query($this->query);
    }

    /**
     * generate TRUNCATE statement.
     *
     * @return object SQL Query
     */
    private function _truncate()
    {
        $this->query = 'TRUNCATE TABLE ';
        $this->query .= $this->_table($this->table);
        $this->query .= ';';

        return $this->query;
    }

    public function truncate()
    {
        $this->_truncate();

        return $this->query;

        return $this->records = Database::query($this->query);
    }

    public function record()
    {
        $this->get();
        Module::import('QueryRecord');

        return new QueryRecord($this->records);
    }

    private function _table()
    {
        $args = func_get_args();

        return "`{$args[0]}`";
    }

    private function _col()
    {
        $args = func_get_args();

        if (is_array($args[0]) && $args[1] != 'string') {
            $temp = array();
            foreach ($args as $arg) {
                foreach ($arg as $param) {
                    array_push($temp, $this->_col($param));
                }
            }

            return $temp;
        }

        if (is_callable($args[0]) && $args[0] instanceof Closure) {
            $q = new self();
            call_user_func($args[0], $q);

            return $q->_get(true);
        }

        if (!is_array($args[0])) {
            $args[0] = trim($args[0]);
        }
        if (strpos($args[0], '~') === 0) {
            return $this->_col(substr($args[0], 1));
        } elseif (substr_count($args[0], ' as ') == 1 && !isset($args[1])) {
            $exp = explode(' as ', $args[0]);
        //    array_push($this->ascol, $exp[1]);
            return $this->_col($exp[0])." AS `{$exp[1]}`";

        // }else if(in_array($args[0],$this->ascol)){
        //     return $this->_col($args[0],"single");
        } elseif (substr_count($args[0], '.') == 1 && !isset($args[1])) {
            $exp = explode('.', $args[0]);
            if($exp[1] == "*"){
                return "`{$exp[0]}`.{$exp[1]}";
            }else{
                return "`{$exp[0]}`.`{$exp[1]}`";
            }
        } elseif (isset($args[1]) && $args[1] == 'single') {
            return "`{$args[0]}`";

        } elseif (isset($args[1]) && $args[1] == 'string') {
            if (is_array($args[0])) {
                return "('".implode("', '", $args[0])."')";
            } else {
                return "'".String::escape($args[0])."'";
            }
        } else {
            if($args[0] == "*"){
                return "`{$this->table}`.{$args[0]}";
            }else{
                return "`{$this->table}`.`{$args[0]}`";
            }

        }
    }

    private function _group()
    {
        $args = func_get_args();
        $arr = array();

        if (!sizeof($this->group)) {
            return '';
        }

        foreach ($this->group as $col) {
            array_push($arr, $this->_col($col));
        }

        return ' GROUP BY '.implode(', ', $arr);
    }

    private function _order()
    {
        $args = func_get_args();
        $arr = array();

        if (!sizeof($this->order)) {
            return '';
        }

        foreach ($this->order as $col) {
            array_push($arr, $this->_col($col[0])." {$col[1]}");
        }

        return ' ORDER BY '.implode(', ', $arr);
    }

    private function _param()
    {
        $args = func_get_args();
        $arr = array();

        if (!sizeof($this->param)) {
            return '*';
        }

        //$this->param = $this->_col($this->param);

        if (isset($args[0]) && $args[0] == 'insert') {
            for ($i = $args[1] - 1; $i < sizeof($this->param); $i += 2) {
                array_push($arr, $this->_col($this->param[$i], $args[1] == 1 ? 'single' : 'string'));
            }
        } elseif (isset($args[0]) && $args[0] == 'update') {
            for ($i = 0; $i < sizeof($this->param); $i += 2) {
                array_push($arr, $this->_col($this->param[$i], 'single').' = '.$this->_col($this->param[$i + 1], 'string'));
            }
        } else {
            foreach ($this->param as $param) {
                array_push($arr, $this->_col($param));
            }
        }

        return implode(', ', $arr);
    }

    public function _where()
    {
        if (!strlen($this->where)) {
            return '';
        }

        //else
        return " WHERE {$this->where}";
    }

    private function _unionParse($args)
    {
        $args = func_get_args();

        if ($args[0] instanceof self) {
            return " UNION {$args[1]}".$args[0]->_get(true);
        } elseif (is_callable($args[0]) && $args[0] instanceof Closure) {
            $q = new self();
            call_user_func($args[0], $q);

            return " UNION $args[1]".$q->_get(true);
        }

        return '';
    }

    private function _joinParse($args, $place = false)
    {
        $this->join_on = '';
        $str = "";
        if($place){
            $str .= " {$place}";
        }
        $str .= ' JOIN ';
        $str .= $this->_table($args[0]);
        array_shift($args);

        if (is_callable($args[0]) && $args[0] instanceof Closure) {
            $q = new self();
            $q->table($this->table);
            call_user_func($args[0], $q);

            return $str.$q->join_on;
        } else {
            call_user_func_array(array($this, 'on'), $args);
            $str .= $this->join_on;
        }

        return $str;
    }

    private function _whereParse($args)
    {
        if (is_callable($args[0]) && $args[0] instanceof Closure) {
            $q = new self();
            $q->table($this->table);
            call_user_func($args[0], $q);

            return "( {$q->where} )";
        }

        return call_user_func_array(array($this, '_parseOperators'), $args);
    }

    private function _parseOperators()
    {
        $args = func_get_args();

        $str = '';

        if (!in_array($args[1], array('set', 'inset'))) {
            $str .= $this->_col($args[0]);
        }

        switch (strtolower($args[1])) {
            case '=':
                $str .= ' = '.$this->_col($args[2], 'string');
                break;

            case 'like':
                $str .= ' LIKE '.$this->_col($args[2], 'string');
                break;

            case '!=':
            $str .= ' != '.$this->_col($args[2], 'string');
            break;

            case '<>':
                $str .= ' <> '.$this->_col($args[2], 'string');
                break;

            case 'lt':
            case '<':
            case '!>':
                $str .= ' < '.$this->_col($args[2], 'string');
                break;

            case 'lte':
            case '<=':
                $str .= ' <= '.$this->_col($args[2], 'string');
                break;

            case 'gt':
            case '>':
            case '!<':
                $str .= ' > '.$this->_col($args[2], 'string');
                break;

            case 'gte':
            case '>=':
                $str .= ' > '.$this->_col($args[2], 'string');
                break;

            case 'is':
                $str .= ' IS '.strtoupper($args[2]);
                break;

            case 'in':
            case 'notin':
                $b = in_array($args[1], array('in')) ? 'IN' : 'NOT IN';

                if ($args[2] instanceof self) {
                    $str .= " {$b} ( ".$args[2]->_get(true).' )';
                } elseif (is_array($args[2])) {
                    $temp = array();
                    foreach ($args[2] as $v) {
                        array_push($temp, $this->_col($v, 'string'));
                    }
                    $str .= " {$b} ( ".implode(', ', $temp).' )';
                } else {
                    $str .= " {$b} ( ".$this->_col($args[2], 'string').' )';
                }
                break;

            case 'bet':
            case 'between':
            case '><':
            case 'notbet':
            case 'notbetween':
            case '!><':

                if (is_array($args[2])) {
                    $str .= " {$b} ".$this->_col($args[2][0], 'string').' AND '.$this->_col($args[2][1], 'string');
                } else {
                    $str .= " {$b} ".$this->_col($args[2], 'string').' AND '.$this->_col($args[3], 'string');
                }
                break;

            case 'set':
            case 'inset':
                if (is_array($args[2])) {
                    $temp = array();
                    foreach ($args[2] as $v) {
                        array_push($temp, 'FIND_IN_SET ('.$this->_col($v, 'string').','.$this->_col($args[0]).')');
                    }
                    $str .= '( '.implode(' AND ', $temp).')';
                } else {
                    $str .= 'FIND_IN_SET ('.$this->_col($args[2], 'string').','.$this->_col($args[0]).')';
                }
                break;

            default:
                $str .= ' = '.$this->_col($args[1], 'string');
                break;
        }

        return $str;
    }
}
