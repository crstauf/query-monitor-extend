<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Collector_Classes extends QM_Collector {

    public $id = 'qmx-classes';

    public function name() {
        return __( 'Classes', 'query-monitor' );
    }

}

function register_qmx_collector_classes( array $collectors, QueryMonitor $qm ) {
	$collectors['qmx-classes'] = new QMX_Collector_Classes;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qmx_collector_classes', 10, 2 );

?>
