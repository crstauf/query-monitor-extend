<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @extends QM_DataCollector<QMX_Data_Time>
 */
class QMX_Collector_Time extends QM_DataCollector {

	public $id = 'time';

	public function name() : string {
		return __( 'Time', 'query-monitor-extend' );
	}

	public function get_storage() : QM_Data {
		require_once 'qmx-time-data.php';
		return new QMX_Data_Time();
	}

	public function process() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$this->data['functions'] = array(
			'UTC'       => 'get_utc',
			'Server'    => 'get_server',
			'WordPress' => 'get_wp',
			'Browser'   => 'get_browser',
		);
	}

	public function get_utc() : string {
		$datetime = date_create( 'now', new DateTimeZone( 'UTC' ) );
		$datetime->setTimezone( new DateTimeZone( 'UTC' ) );

		return $datetime->format( 'D, M j, Y H:i:s' );
	}

	public function get_server() : string {
		$datetime = date_create( 'now', new DateTimeZone( 'UTC' ) );

		if ( ! empty( ini_get( 'date.timezone' ) ) ) {
			$datetime->setTimezone( new DateTimeZone( ini_get( 'date.timezone' ) ) );
		}

		return $datetime->format( 'D, M j, Y H:i:s T' );
	}

	public function get_server_offset() : string {
		$datetime = date_create( "now", new DateTimeZone( 'UTC' ) );

		if ( ! empty( ini_get( 'date.timezone' ) ) ) {
			$datetime->setTimezone( new DateTimeZone( ini_get( 'date.timezone' ) ) );
		}

		return $datetime->format( 'Z' );
	}

	public function get_server_timezone() : string {
		$datetime = date_create( "now", new DateTimeZone( 'UTC' ) );

		if ( ! empty( ini_get( 'date.timezone' ) ) ) {
			$datetime->setTimezone( new DateTimeZone( ini_get( 'date.timezone' ) ) );
		}

		return $datetime->format( 'T' );
	}

	public function get_wp() : string {
		return current_time( 'D, M j, Y H:i:s T' );
	}

	public function get_wp_offset() : float {
		return ( float ) get_option( 'gmt_offset' );
	}

	public function get_wp_timezone() : string {
		return current_time( 'T' );
	}

	public function get_browser() : string {
		return '-';
	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['time'] = new QMX_Collector_Time;
	return $collectors;
} );
