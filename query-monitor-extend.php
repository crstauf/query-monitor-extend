<?php
/**
 * Plugin Name: Query Monitor Extend
 * Plugin URI: https://github.com/crstauf/query-monitor-extend
 * Description: Additional panels for Query Monitor by John Blackbourn.
 * Version: 1.4.3
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 *
 * QM tested up to: 3.13.0
 */

defined( 'WPINC' ) || die();

defined( 'QMX_DISABLED' ) || define( 'QMX_DISABLED', false );
defined( 'QMX_TESTED_WITH_QM' ) || define( 'QMX_TESTED_WITH_QM', '3.13.0' );

if ( defined( 'QM_DISABLED' ) && ! constant( 'QM_DISABLED' ) ) {
	return;
}

if ( constant( 'QMX_DISABLED' ) ) {
	return;
}

/**
 * Filter: plugin_row_meta
 *
 * - add "Tested up to" notice
 * - add "Requires QM" notice
 *
 * @param array $meta
 * @param string $file
 * @return string[]
 */
add_filter( 'plugin_row_meta', static function ( array $meta, string $file ) : array {
	if ( 'query-monitor-extend/query-monitor-extend.php' !== $file )
		return $meta;

	$first = array_shift( $meta );

	array_unshift(
		$meta,
		$first,
		sprintf(
			'Tested up to <a href="%1$s" rel="noopener noreferrer">Query Monitor</a> <a href="%2$s%3$s" rel="noopener noreferrer">%3$s</a>',
			'https://wordpress.org/plugins/query-monitor/',
			'https://github.com/johnbillion/query-monitor/releases/tag/',
			QMX_TESTED_WITH_QM,
		)
	);

	if ( class_exists( 'QueryMonitor' ) )
		return $meta;

	$first = array_shift( $meta );

	array_unshift(
		$meta,
		$first,
		sprintf(
			'Requires <a href="%1$s" rel="noopener noreferrer">Query Monitor</a>',
			'https://wordpress.org/plugins/query-monitor/'
		)
	);

	return $meta;
}, 10, 2 );

if ( !class_exists( 'QueryMonitor' ) || did_action( 'qm/cease' ) )
	return;

$collector_names = array(
	'acf',
	'constants',
	'files',
	'heartbeat',
	'image-sizes',
	'paths',
	'time',
);

$dir = trailingslashit( __DIR__ );

# Include all collector and outputters.
foreach ( $collector_names as $collector_name ) {
	include_once sprintf( '%1$s%2$s/qmx-%2$s-collector.php', $dir, $collector_name );
	include_once sprintf( '%1$s%2$s/qmx-%2$s-output.php',    $dir, $collector_name );

	$function_name = sprintf( 'load_qmx_%s_collector', str_replace( '-', '_', $collector_name ) );

	if ( !function_exists( $function_name ) )
		continue;

	$function_name( 'query-monitor/query-monitor.php' );
}

# Include additional conditionals.
include_once $dir . 'qmx-conditionals.php';
