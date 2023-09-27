<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @property-write QMX_Data_Files $data
 */
class QMX_Collector_Files extends QM_DataCollector {

	public $id = 'files';

	public function name() : string {
		return __( 'Files', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-files-data.php';
		return new QMX_Data_Files();
	}

	public function process() {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$files_with_errors = array();
		$collector         = QM_Collectors::get( 'php_errors' );

		if ( ! is_null( $collector ) ) {
			$php_errors = $collector->get_data();

			if ( ! empty( $php_errors['errors'] ) ) {
				foreach ( $php_errors['errors'] as $type => $errors ) {
					foreach ( $errors as $error ) {
						$files_with_errors[ $error['file'] ] = 1;
					}
				}
			}
		}

		foreach ( get_included_files() as $i => $filepath ) {
			$this->data->files[] = array(
				'path'      => $filepath,
				'component' => QM_Util::get_file_component( $filepath ),
				'has_error' => array_key_exists( $filepath, $files_with_errors ),
			);
		}
	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['files'] = new QMX_Collector_Files;
	return $collectors;
} );