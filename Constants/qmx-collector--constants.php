<?php
/**
 * Query Monitor Constants collector.
 */

defined( 'WPINC' ) || die();

add_action( 'plugin_loaded', 'load_qmx_constants_collector' );

function load_qmx_constants_collector( string $file ) {

	if ( 'query-monitor/query-monitor.php' !== plugin_basename( $file ) )
		return;

	remove_action( 'plugin_loaded', __FUNCTION__ );

	if ( !class_exists( 'QueryMonitor' ) )
		return;

	if ( defined( 'QMX_DISABLE' ) && QMX_DISABLE )
		return;

	class QMX_Collector_Constants extends QM_Collector {

		public $id = 'constants';

		public function name() {
			return __( 'Constants', 'query-monitor-extend' );
		}

		public function process() {

			$constants = get_defined_constants( true );
			$this->data['constants'] = $constants['user'];

		}

	}

	add_filter( 'qm/collectors', static function ( array $collectors ) : array {
		$collectors['constants'] = new QMX_Collector_Constants;
		return $collectors;
	} );

}