<?php declare(strict_types=1);

namespace QMX\Collector;

defined( 'WPINC' ) || die();

/**
 * @extends \QM_DataCollector<\QMX\Data\Files>
 * @property-write \QMX\Data\Files $data
 */
class Files extends \QM_DataCollector {

	public $id = 'files';

	public function name() : string {
		return __( 'Files', 'query-monitor-extend' );
	}

	public function get_storage(): \QM_Data {
		return new \QMX\Data\Files();
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
