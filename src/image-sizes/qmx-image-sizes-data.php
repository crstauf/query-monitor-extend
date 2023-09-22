<?php
/**
 * Plugin Name: QMX: Image Sizes Data
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/image-sizes
 * Description: Query Monitor data for image sizes.
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

add_action( 'qmx/load_data/image_sizes', static function () {

	class QMX_Data_Image_Sizes extends QM_Data {

		public $sizes = array();
		public $duplicates = array();

	}

} );