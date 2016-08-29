<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Collector_Constants extends QM_Collector {

    public $id = 'qmx-constants';

    public function name() {
        return __( 'Constants', 'query-monitor' );
    }

}

function register_qmx_collector_constants( array $collectors, QueryMonitor $qm ) {
	$collectors['qmx-constants'] = new QMX_Collector_Constants;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qmx_collector_constants', 10, 2 );

?>
