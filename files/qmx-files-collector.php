<?php

defined( 'WPINC' ) || die();

class QMX_Collector_Files extends QM_DataCollector {

	public $id = 'files';

	public function name() {
		return __( 'Files', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		do_action( 'qmx/load_data/files' );
		return new QMX_Data_Files();
	}

	public function process() {
		if ( did_action( 'qm/cease' ) )
			return;

		$php_errors = QM_Collectors::get( 'php_errors' )->get_data();
		$files_with_errors = array();

		if ( !empty( $php_errors['errors'] ) )
			foreach ( $php_errors['errors'] as $type => $errors )
				foreach ( $errors as $error )
					$files_with_errors[$error['file']] = 1;

		foreach ( get_included_files() as $i => $filepath )
			$this->data->files[] = array(
				'path' => $filepath,
				'component' => QM_Util::get_file_component( $filepath ),
				'has_error' => array_key_exists( $filepath, $files_with_errors ),
			);

	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['files'] = new QMX_Collector_Files;
	return $collectors;
} );