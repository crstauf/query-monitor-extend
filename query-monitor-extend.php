<?php
/**
 * Query Monitor Extend plugin for WordPress
 *
 * @package query-monitor-extend
 * @link    https://github.com/crstauf/query-monitor-extend
 * @author  Caleb Stauffer <develop@calebstauffer.com>
 *
 * Plugin Name: Query Monitor Extend
 * Plugin URI: https://github.com/crstauf/query-monitor-extend
 * Description: Enhancements and extensions for the awesome Query Monitor plugin by John Blackbourn
 * Version: 1.0
 * Author: Caleb Stauffer
 * Author URI: http://develop.calebstauffer.com
 * QM tested up to: 3.3.1
*/

if ( !defined( 'ABSPATH' ) || !function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if (
	   'cli' === php_sapi_name()
	|| ( defined( 'DOING_CRON'   ) && DOING_CRON   )
	|| ( defined( 'QM_DISABLED'  ) && QM_DISABLED  )
	|| ( defined( 'QMX_DISABLED' ) && QMX_DISABLED )
	|| !class_exists( 'QueryMonitor' )
)
	return;

$qmx_dir = dirname( __FILE__ );

require_once "{$qmx_dir}/classes/Plugin.php";

foreach ( array( 'QueryMonitorExtend', 'Collectors', 'Collector', 'Output' ) as $qmx_class ) {
	require_once "{$qmx_dir}/classes/{$qmx_class}.php";
}

include_once "{$qmx_dir}/output/AdminBar.php";

QueryMonitorExtend::init( __FILE__ );
?>
