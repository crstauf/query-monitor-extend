<?php
/**
 * Time collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_Time extends QMX_Collector {

	public $id = 'time';

	public function name() {
		return __( 'Time', 'query-monitor-extend' );
	}

	function process() {

		$this->data['functions'] = array(
			'UTC'       => 'get_utc',
			'Server'    => 'get_server',
			'WordPress' => 'get_wp',
			'Browser'   => 'get_browser',
		);

	}

	function get_utc() {
		$datetime = date_create( "now", new DateTimeZone( 'UTC' ) );
		$datetime->setTimezone( new DateTimeZone( 'UTC' ) );
		return $datetime->format( 'D, M j, Y H:i:s' );
	}

	function get_server() {
		$datetime = date_create( "now", new DateTimeZone( 'UTC' ) );

		if ( !empty( ini_get( 'date.timezone' ) ) )
			$datetime->setTimezone( new DateTimeZone( ini_get( 'date.timezone' ) ) );

		return $datetime->format( 'D, M j, Y H:i:s' );
	}

	function get_server_offset() {
		$datetime = date_create( "now", new DateTimeZone( 'UTC' ) );

		if ( !empty( ini_get( 'date.timezone' ) ) )
			$datetime->setTimezone( new DateTimeZone( ini_get( 'date.timezone' ) ) );

		return $datetime->format( 'Z' );
	}

	function get_wp() {
		return current_time( 'D, M j, Y H:i:s' );
	}

	function get_wp_offset() {
		return get_option( 'gmt_offset' );
	}

	function get_browser() {
		return '-';
	}

}

function register_qmx_collector_time( array $collectors, QueryMonitorExtend $qmx ) {
	$collectors['time'] = new QMX_Collector_Time;
	return $collectors;
}

add_filter( 'qmx/collectors', 'register_qmx_collector_time', 10, 2 );