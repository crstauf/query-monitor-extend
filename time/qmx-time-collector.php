<?php
/**
 * Plugin Name: QMX: Time Collector
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/time
 * Description: Query Monitor collector for time.
 * Version: 1.0.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

defined( 'QMX_DISABLED' ) || define( 'QMX_DISABLED', false );
defined( 'QMX_TESTED_WITH_QM' ) || define( 'QMX_TESTED_WITH_QM', '3.13.0' );

add_action( 'plugin_loaded', 'load_qmx_time_collector' );

function load_qmx_time_collector( string $file ) {

	if ( 'query-monitor/query-monitor.php' !== plugin_basename( $file ) )
		return;

	remove_action( 'plugin_loaded', __FUNCTION__ );

	if ( !class_exists( 'QueryMonitor' ) )
		return;

	if ( defined( 'QM_DISABLED' ) && ! constant( 'QM_DISABLED' ) ) {
		return;
	}

	if ( constant( 'QMX_DISABLED' ) ) {
		return;
	}

	class QMX_Collector_Time extends QM_DataCollector {

		public $id = 'time';

		public function name() {
			return __( 'Time', 'query-monitor-extend' );
		}

		public function get_storage(): QM_Data {
			require_once 'qmx-time-data.php';
			return new QMX_Data_Time();
		}

		function process() {
			if ( did_action( 'qm/cease' ) )
				return;

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

			return $datetime->format( 'D, M j, Y H:i:s T' );
		}

		function get_server_offset() {
			$datetime = date_create( "now", new DateTimeZone( 'UTC' ) );

			if ( !empty( ini_get( 'date.timezone' ) ) )
				$datetime->setTimezone( new DateTimeZone( ini_get( 'date.timezone' ) ) );

			return $datetime->format( 'Z' );
		}

		function get_server_timezone() {
			$datetime = date_create( "now", new DateTimeZone( 'UTC' ) );

			if ( !empty( ini_get( 'date.timezone' ) ) )
				$datetime->setTimezone( new DateTimeZone( ini_get( 'date.timezone' ) ) );

			return $datetime->format( 'T' );
		}

		function get_wp() {
			return current_time( 'D, M j, Y H:i:s T' );
		}

		function get_wp_offset() {
			return get_option( 'gmt_offset' );
		}

		function get_wp_timezone() {
			return current_time( 'T' );
		}

		function get_browser() {
			return '-';
		}

	}

	add_filter( 'qm/collectors', static function ( array $collectors ) : array {
		$collectors['time'] = new QMX_Collector_Time;
		return $collectors;
	} );

}