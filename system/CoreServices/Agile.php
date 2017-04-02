<?php
namespace App\System;

abstract class Agile extends Query
{

    public static $__query = [];

    public static $table;
    public static $instance;
    public static $instance_sql;
    public static $record;
    public static $response;

    public static $primary = 'id';
    public static $accessable = [];
    public static $editable = [];
    public static $relations = [];
    
    public static function initialize()
    {
        if (!static::$table) {
            $class = explode('\\', get_called_class());
            $class = end($class);
            $class = Str::camelToSnake($class);
            static::$table = $class;
        }
        if (!static::$instance) {
            static::$instance = new static;
            static::$instance->table(static::$table);
        }
    }

    public function __construct()
    {
    }

    public function __invoke()
    {
        return static::$instance->_data();
    }

    public function __get($key)
    {
        self::initialize();
        static::$instance->_data();
        return isset(static::$record->{$key}) ? static::$record->{$key} : null;
    }
    public function __set($key, $value)
    {
        self::initialize();
        static::$instance->_clear();
        if ($value) {
            if ($key == static::$primary) {
                static::$instance->where($key, $value);
            } else {
                if (in_array($key, static::$editable)) {
                    static::$instance->param($key, $value);
                } else {
                    die("Error: Agile say, column <b>{$key}</b> now allowed to modify.");
                    // throw new \Exception("Agile say, column {$key} now allowed to edit");
                }
            }
        }
    }

    function __call($name, $arguments)
    {
        self::initialize();
        return static::$instance->_callable($name, $arguments);
    }
    
    static function __callStatic($name, $arguments)
    {
        self::initialize();
        return static::$instance->_callable($name, $arguments);
    }
    
    function _callable($name, $arguments)
    {
        static::$instance->_clear();

        if (strpos($name, 'get') === 0) {
            return static::$instance->_getter($name);
        }
        if (strpos($name, 'with') === 0) {
            return static::$instance->_wither($name);
        }

        return call_user_func_array([static::$instance, '_' . $name], $arguments);
    }

    public function _getter($name)
    {
        $name = Str::camelToSnake(substr($name, 3));
        static::$instance->param($name);
        return static::$instance;
    }

    public function _wither($name)
    {
        // add with here
        return static::$instance;
    }

    private function _all()
    {
        // TODO: set this as alias to find
        return static::$instance;
    }

    public function _find()
    {
        $arguments = Loader::mergeArguments(func_get_args());
        if (sizeof($arguments)) {
            $first = array_shift($arguments);
            if (!is_null($first)) {
                static::$instance->where('id', $first);
            }
            
            foreach ($arguments as $value) {
                if (!is_null($value)) {
                    static::$instance->orWhere('id', $value);
                }
            }
        }
        return static::$instance;
    }

    public function _data()
    {
        if (!static::$response) {
            // get only accessable stuff
            foreach (static::$accessable as $access) {
                static::$instance->param($access);
            }
            static::$instance_sql = $this->_get();
            $records = static::$instance->get()->record;
            static::$record = isset($records[0]) ? $records[0] : new \stdClass;
            static::$response = $records;
        }
        return static::$response;
    }

    public function _record($idx)
    {
        // static::$instance->_clear();
        static::$instance->_data();
        static::$record = isset(static::$response[$idx]) ? static::$response[$idx] : new \stdClass;
        
        return static::$response;
    }

    public function _clear()
    {
        if (static::$instance_sql != static::$instance->_get()) {
            static::$record = null;
            static::$response = null;
        }

        return static::$instance;
    }

    public function _reset()
    {
        static::$instance = null;
        static::$instance_sql = null;
        static::$record = null;
        static::$response = null;
        self::initialize();

        return static::$instance;
    }

    public function _save()
    {
        $args = func_get_args();
        if (isset($args[0])) {
            $this->map($args[0]);
        }

        if (!sizeof(static::$instance->param)) {
            static::$response = [
                'last' => null,
                'count' => null
            ];
        } else {
            $set = static::$instance->set();//->last;
            $this->_reset();

            static::$response = [
            'last' => isset($set->last) ? $set->last : null,
            'count' => isset($set->count) ? $set->count : null,
            ];
        }
        return static::$instance;
    }

    public function _delete()
    {
        $del = static::$instance->_find(func_get_args())->remove();
        $this->_reset();

        static::$response = [
            'count' => $del->count
        ];
        return static::$instance;
    }

    public function _map()
    {
        $dyn = func_get_arg(0);
        $dyn->each(function ($val, $key) {
            $this->__set($key, $val);
        });
        return $this;
    }

/*
    TODO: Functions
        public function orFail(){}
        public function cursor(){}
        public function chunk(){}
        public function paginate(){}
        public function advancePaginate(){}

    TODO: joinable, withable
    TODO: paginate
    {
        page: {
            current: 1,
            last: 10, // only in advanced
            rate: 15  // records per page
        },
        cursor: {
            from: 1,
            to: 15,
            total: 50 // only in advanced
        },
        records: []
    }
*/
}
