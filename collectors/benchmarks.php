<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Collector_Benchmarks extends QM_Collector {

    public $id = 'qmx-benchmarks';
    public $time_limit;
    public $memory_limit;

    public function name() {
        return __( 'Benchmarks', 'query-monitor' );
    }

    public function __construct() {

        global $wpdb;

        parent::__construct();

        $this->db_queries = 0;
        $this->data['benchmarks'] = array();

    }

    public function add_data($label = false) {

        $now = array();

        $now['label'] = $label;
        $now['i'] = count( $this->data['benchmarks'] );
        $now['timestamp'] = time();

        $now['time'] = self::timer_stop_float();

		if ( function_exists( 'memory_get_peak_usage' ) ) {
			$now['memory'] = memory_get_peak_usage();
		} else if ( function_exists( 'memory_get_usage' ) ) {
			$now['memory'] = memory_get_usage();
		} else {
			$now['memory'] = 0;
		}

		$now['included_files'] = count( get_included_files() );

        if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
            global $wpdb;
            $queries = array_slice( $wpdb->queries, $this->db_queries );
            $this->db_queries = count( $wpdb->queries );

            $db_query_time = 0;
            $db_query_types = array();

            foreach ( $queries as $query ) {
                $db_query_time += $query[1];
                $mysql = explode( ' ', $query[0] );
                $db_query_types[trim( $mysql[0] )] = array_key_exists( $mysql[0], $db_query_types ) ? $db_query_types[$mysql[0]] + 1 : 1;
            }

            $now['db_query_time'] = $db_query_time;
            $now['db_query_types'] = $db_query_types;
        }

        $this->data['benchmarks'][] = $now;

    }

}

QM_Collectors::add( new QMX_Collector_Benchmarks );

function QMX_Benchmark($label = false) {
    if ( $collector = QM_Collectors::get( 'qmx-benchmarks' ) )
        $collector->add_data($label);
}

QMX_Benchmark( 'first' );

?>
