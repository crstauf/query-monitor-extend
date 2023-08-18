<?php
/**
 * Plugin Name: QMX: ACF Data
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/acf
 * Description: Query Monitor data for ACF collector.
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

add_action( 'qmx/load_data/acf', static function () {

	class QMX_Data_ACF extends QM_Data {

		public $fields = array();
		public $field_keys = array();
		public $post_ids = array();
		public $callers = array();
		public $counts = array();
		public $field_groups = array();
		public $local_json = array();
		public $loaded_field_groups = array();

	}

} );