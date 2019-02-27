<?php
/**
 * Constants collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_Constants extends QMX_Collector {

	public $id = 'constants';

	public function name() {
		return __( 'Constants', 'query-monitor-extend' );
	}

	public function process() {

		$constants = get_defined_constants( true );
		$this->data['constants'] = $constants['user'];

	}

}

function register_qmx_collector_constants( array $collectors, QueryMonitorExtend $qmx ) {
	$collectors['constants'] = new QMX_Collector_Constants;
	return $collectors;
}

add_filter( 'qmx/collectors', 'register_qmx_collector_constants', 10, 2 );