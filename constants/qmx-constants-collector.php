<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @extends QM_DataCollector<QMX_Data_Constants>
 * @qm-collectors constants
 */
class QMX_Collector_Constants extends QM_DataCollector {

	public $id = 'constants';

	public function name() : string {
		return __( 'Constants', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-constants-data.php';
		return new QMX_Data_Constants();
	}

	public function process() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$constants = get_defined_constants( true );

		$this->data['constants'] = $constants['user'];
	}

}
