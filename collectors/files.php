<?php
/**
 * Files collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_Files extends QMX_Collector {

	public $id = 'files';

	public function name() {
		return __( 'Files', 'query-monitor-extend' );
	}

	public function process() {
		$php_errors = QM_Collectors::get( 'php_errors' )->get_data();
		$files_with_errors = array();

		if ( !empty( $php_errors['errors'] ) )
			foreach ( $php_errors['errors'] as $type => $errors )
				foreach ( $errors as $error )
					$files_with_errors[$error['file']] = 1;

		foreach ( get_included_files() as $i => $filepath )
			$this->data['files'][] = array(
				'path' => $filepath,
				'component' => QM_Util::get_file_component( $filepath ),
				'has_error' => array_key_exists( $filepath, $files_with_errors ),
			);

	}

}

function register_qmx_collector_files( array $collectors, QueryMonitorExtend $qmx ) {
	$collectors['files'] = new QMX_Collector_Files;
	return $collectors;
}

add_filter( 'qmx/collectors', 'register_qmx_collector_files', 10, 2 );