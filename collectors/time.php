<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Collector_Time extends QM_Collector {

    public $id = 'qmx-time';

    public function name() {
        return __( 'Time', 'query-monitor' );
    }

    public function __construct() {

        global $wpdb;

        parent::__construct();

    }

}

function register_qmx_collector_time( array $collectors, QueryMonitor $qm ) {
	$collectors['qmx-time'] = new QMX_Collector_Time;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qmx_collector_time', 10, 2 );

?>
