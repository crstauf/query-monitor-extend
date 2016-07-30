<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Collector_VarDumps extends QM_Collector {

    public $id = 'qmx-var_dumps';

    public function name() {
        return __( 'Var Dumps (' . count( cssllc_query_monitor_extend::$var_dumps ) . ')', 'query-monitor' );
    }

    public function __construct() {

        global $wpdb;

        parent::__construct();

    }

    public function process() {

        $this->data['qmx-var_dumps'] = cssllc_query_monitor_extend::$var_dumps;

    }

}

function register_qmx_collector_vardumps( array $collectors, QueryMonitor $qm ) {
	$collectors['qmx-var_dumps'] = new QMX_Collector_VarDumps;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qmx_collector_vardumps', 10, 2 );

?>
