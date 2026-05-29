<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @extends QM_DataCollector<QMX_Data_Globals_Post>
 */
class QMX_Collector_Globals_Post extends QM_DataCollector {

	public $id = 'globals-post';

	public function name() : string {
		return __( '$_POST', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-globals-post-data.php';
		return new QMX_Data_Globals_Post();
	}

	public function process() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$this->data['post'] = $_POST ?? [];
	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['globals-post'] = new QMX_Collector_Globals_Post;
	return $collectors;
} );
