<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @extends QM_DataCollector<QMX_Data_Globals>
 */
class QMX_Collector_Globals extends QM_DataCollector {

	public $id = 'globals';

	public function name() : string {
		return __( 'SERVER, GET, POST', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-globals-data.php';
		return new QMX_Data_Globals();
	}

	public function process() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$this->data['server'] = $_SERVER;

		if ( ! empty( $_GET ) ) {
			$this->data['get'] = $_GET;
		}

		if ( ! empty( $_POST ) ) {
			$this->data['post'] = $_POST;
		}
	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['globals'] = new QMX_Collector_Globals;
	return $collectors;
} );
