<?php
/**
 * Plugin Name: QMX: Constants Collector
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/constants
 * Description: Query Monitor collector for constants.
 * Version: 1.0.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

defined( 'QMX_DISABLED' ) || define( 'QMX_DISABLED', false );
defined( 'QMX_TESTED_WITH_QM' ) || define( 'QMX_TESTED_WITH_QM', '3.13.0' );

add_action( 'plugin_loaded', 'load_qmx_constants_collector' );

function load_qmx_constants_collector( string $file ) {

	if ( 'query-monitor/query-monitor.php' !== plugin_basename( $file ) )
		return;

	remove_action( 'plugin_loaded', __FUNCTION__ );

	if ( !class_exists( 'QueryMonitor' ) )
		return;

	if ( defined( 'QM_DISABLED' ) && constant( 'QM_DISABLED' ) ) {
		return;
	}

	if ( constant( 'QMX_DISABLED' ) ) {
		return;
	}

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

}