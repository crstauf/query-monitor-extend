<?php
/**
 * Plugin Name: QMX: Paths Data
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/paths
 * Description: Query Monitor data for paths.
 * Version: 1.0.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

if ( defined( 'QM_DISABLED' ) && constant( 'QM_DISABLED' ) ) {
	return;
}

if ( constant( 'QMX_DISABLED' ) ) {
	return;
}

add_action( 'qmx/load_data/paths', static function () {

	class QMX_Data_Paths extends QM_Data {

		public $paths = array();

	}

} );