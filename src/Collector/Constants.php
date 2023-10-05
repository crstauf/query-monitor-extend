<?php declare(strict_types=1);

namespace QMX\Collector;

defined( 'WPINC' ) || die();

/**
 * @extends \QM_DataCollector<\QMX\Data\Constants>
 */
class Constants extends \QM_DataCollector {

	public $id = 'constants';

	public function name() : string {
		return __( 'Constants', 'query-monitor-extend' );
	}

	public function get_storage(): \QM_Data {
		return new \QMX\Data\Constants();
	}

	public function process() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$constants = get_defined_constants( true );

		$this->data['constants'] = $constants['user'];
	}

}
