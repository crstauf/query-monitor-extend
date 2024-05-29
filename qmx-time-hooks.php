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

	$label    = 'Action: ' . current_action();
	$action   = sprintf( 'qm/%s', $start_lap_stop );
	$priority = $wp_filter[ current_action() ]->current_priority();

	do_action( $action, $label );
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
add_action( 'init', 'qmx_lap', 5 );
add_action( 'init', 'qmx_stop', 50 );