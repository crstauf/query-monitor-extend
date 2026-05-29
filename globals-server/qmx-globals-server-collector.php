<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @extends QM_DataCollector<QMX_Data_Globals_Server>
 */
class QMX_Collector_Globals_Server extends QM_DataCollector {

	public $id = 'globals-server';

	public function name() : string {
		return __( '$_SERVER', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-globals-server-data.php';
		return new QMX_Data_Globals_Server();
	}

	public function process() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$this->data['server'] = $_SERVER;
	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['globals-server'] = new QMX_Collector_Globals_Server;
	return $collectors;
} );
