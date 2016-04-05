<?php

defined('VERSION') or exit('Direct access to this location is not permitted');

/**
 * codeHive Benchmark.
 *
 * Benchmark class create time and resource listener to monitor code
 *
 * @category    core
 *
 * @author      Tamer Zorba <abo.al.tot@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, PureCore International Solutions (http://purecis.com/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 *
 * @link       http://codehive.purecis.com/package/Benchmark
 * @since      File available since Release 2.0.0
 *
 * @version    V: 2.1.0
 */
class Benchmark
{
    /**
     * List of benchmarks.
     */
    protected static $benchmarks = array();

    // --------------------------------------------------------------------

    /**
     * Benchmark Start.
     *
     * @param	string 	Benchmark name
     *
     * @return array
     */
    public static function start($name = 'default')
    {
        self::$benchmarks[$name] = new stdClass();
        self::$benchmarks[$name]->time = microtime(true);
        self::$benchmarks[$name]->memory = memory_get_usage();

        return self::$benchmarks[$name];
    }

    // --------------------------------------------------------------------

    /**
     * Benchmark Complete.
     *
     * @param	string 	Benchmark name
     *
     * @return array
     */
    public static function complete($name = 'default')
    {
        global $config;

        $end = new stdClass();
        $end->time = microtime(true);
        $end->memory = memory_get_usage();

        $result = new stdClass();
        $result->time = round($end->time - self::$benchmarks[$name]->time, 4);
        $result->memory = $end->memory - self::$benchmarks[$name]->memory;
        $result->memory .= ' - '.File::format($result->memory);

        if (strtoupper($config['ENVIRONMENT']) == 'TRACE') {
            Trace::error("Benchmark {$name}", "Time : {$result->time} | Memory : {$result->memory}");
        }

        return $result;
    }
}

/* End of file Benchmark.php */
/* Location: ./system/core/Benchmark.php */
