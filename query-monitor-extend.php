<?php
/**
 * Plugin Name: Query Monitor Extend
 * Plugin URI: https://github.com/crstauf/query-monitor-extend
 * Description: Additional panels for Query Monitor by John Blackbourn.
 * Version: 1.5.2
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 *
 * QM tested up to: 3.13.1
 */

defined( 'WPINC' ) || die();

if ( ! defined( 'QMX_DISABLED' ) ) {
	define( 'QMX_DISABLED', false );
}

if ( ! defined( 'QMX_TESTED_WITH_QM' ) ) {
	define( 'QMX_TESTED_WITH_QM', '3.13.1' );
}

if ( constant( 'QM_DISABLED' ) ) {
	return;
}

if ( constant( 'QMX_DISABLED' ) ) {
	return;
}

// Prevent mu-plugin and plugin combo.
if ( defined( 'QMX_LOADED' ) ) {
	trigger_error( sprintf( 'Query Monitor Extend loaded previously: %s', constant( 'QMX_LOADED' ) ), E_USER_NOTICE );
	return;
}

define( 'QMX_LOADED', __FILE__ );

if ( ! class_exists( 'QueryMonitor' ) || did_action( 'qm/cease' ) ) {
	return;
}

spl_autoload_register( static function ( $class_name ) {
    // A nice autoloader.
});

add_action( 'plugins_loaded', array( QMX\Plugin::class, 'boot' ), 10, 0 );
