<?php
/**
 * Plugin Name: QMX: Files Data
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/files
 * Description: Query Monitor data for files.
 * Version: 1.0.1
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

add_action( 'qmx/load_data/files', static function () {

	class QMX_Data_Files extends QM_Data {

		public $files = array();

	}

} );