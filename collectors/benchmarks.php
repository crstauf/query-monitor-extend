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

    public function add_data($label = false,$file_line = false,$timestamp = null) {
        global $wpdb;

        if ( is_array( $file_line ) )
            $file_line = str_replace(
                ABSPATH,
                './',
                $file_line[0] . ':' . $file_line[1]
            );

        if ( empty( $timestamp ) )
            $timestamp = time();

        $now = array();

        $now['label'] = $label;
        $now['file_line'] = $file_line;
        $now['i'] = count( $this->data['benchmarks'] );
        $now['timestamp'] = $timestamp;

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
            if ( !is_array( $wpdb->queries ) )
                $wpdb->queries = ( array ) $wpdb->queries;

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

    function add_early_data( $now ) {
        $this->data['benchmarks'][] = $now;
    }

}

QM_Collectors::add( new QMX_Collector_Benchmarks );

function QMX_Benchmark($label = false,$file_line = false,$timestamp = null) {
    if ( $collector = QM_Collectors::get( 'qmx-benchmarks' ) ) {

        if ( empty( $timestamp ) )
            $timestamp = time();

        $data = $collector->get_data();

        if (
            did_action( 'send_headers' )
            || did_action( 'admin_init' )
        ) {

            if ( function_exists( 'wp_get_current_user' ) ) {
                if ( current_user_can( 'administrator' ) )
                    echo '<!-- QMX Benchmark ' . ( count( $data['benchmarks'] ) + 1 ) . ': ' . ( false === $label ? $timestamp : esc_html( $label ) ) . ( !empty( $file_line ) ? ', ' . str_replace( ABSPATH, './', $file_line[0] ) . ':' . $file_line[1] : '' ) . ' -->';
            } else
                echo '<!-- QMX Benchmark ' . ( count( $data['benchmarks'] ) + 1 ) . ': ' . ( false === $label ? $timestamp : esc_html( $label ) ) . ( !empty( $file_line ) ? ', ' . str_replace( ABSPATH, './', $file_line[0] ) . ':' . $file_line[1] : '' ) . ' -->';

        }

        $collector->add_data($label,$file_line,$timestamp);
    }
}

function QMX_Early_Benchmark( $now ) {
    if ( $collector = QM_Collectors::get( 'qmx-benchmarks' ) )
        $collector->add_early_data( $now );
}

?>
