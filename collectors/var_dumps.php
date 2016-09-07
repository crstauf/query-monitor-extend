<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Collector_VarDumps extends QM_Collector {

    public $id = 'qmx-var_dumps';

    public function name() {
        return __( 'Var Dumps (' . count( query_monitor_extend::$var_dumps ) . ')', 'query-monitor' );
    }

    public function __construct() {

        global $wpdb;

        parent::__construct();

    }

    public function add( $label, $var, $time ) {
        $this->data['vardumps'][microtime()] = array( 'label' => $label, 'var' => $var, 'timestamp' => $time );
    }

}

function register_qmx_collector_vardumps( array $collectors, QueryMonitor $qm ) {
	$collectors['qmx-var_dumps'] = new QMX_Collector_VarDumps;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qmx_collector_vardumps', 10, 2 );

if ( !function_exists('qmx_dump') ) {
	function qmx_dump( $var, $label = 'Unknown', $time = false ) {
        if ( false === $time )
            $time = time();
        QM_Collectors::get( 'qmx-var_dumps' )->add( $label, $var, $time );
	}
}

if ( !function_exists('qm_dump') ) {
    function qm_dump( $var, $label = 'Unknown' ) {
        qmx_dump( $var, $label, time() );
    }
}

?>
