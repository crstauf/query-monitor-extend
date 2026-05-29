<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @extends QM_DataCollector<QMX_Data_Globals>
 */
class QMX_Collector_Globals_Get extends QM_DataCollector {

	public $id = 'globals-get';

	public function name() : string {
		return __( '$_GET', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-globals-get-data.php';
		return new QMX_Data_Globals_Get();
	}

	public function process() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$this->data['get'] = $_GET ?? [];
	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['globals-get'] = new QMX_Collector_Globals_Get;
	return $collectors;
} );
