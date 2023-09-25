<?php

defined( 'WPINC' ) || die();

class QMX_Collector_Constants extends QM_DataCollector {

	public $id = 'constants';

	public function name() {
		return __( 'Constants', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-constants-data.php';
		return new QMX_Data_Constants();
	}

	public function process() {
		if ( did_action( 'qm/cease' ) )
			return;

		$constants = get_defined_constants( true );
		$this->data['constants'] = $constants['user'];

	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['constants'] = new QMX_Collector_Constants;
	return $collectors;
} );