<?php

defined( 'WPINC' ) || die();

function qmx_time_hooks( string $start_lap_stop ) : void {
	if ( ! doing_action() ) {
		return;
	}

	if ( ! in_array( $start_lap_stop, array( 'start', 'lap', 'stop' ) ) ) {
		return;
	}

	global $wp_filter;

	$label    = current_action();
	$action   = sprintf( 'qm/%s', $start_lap_stop );
	$priority = '';

	if ( 'lap' === $start_lap_stop ) {
		$priority = $wp_filter[ current_action() ]->current_priority();
	}

	do_action( $action, $label, $priority );
}

/**
 * @return void
 */
function qmx_start() : void {
	qmx_time_hooks( 'start' );
}

/**
 * @return void
 */
function qmx_lap() : void {
	qmx_time_hooks( 'lap' );
}

/**
 * @return void
 */
function qmx_stop() : void {
	qmx_time_hooks( 'stop' );
}

add_action( 'init', 'qmx_start', 0 );
add_action( 'init', 'qmx_lap', 9 );
add_action( 'init', 'qmx_lap', 11 );
add_action( 'init', 'qmx_stop', 25 );