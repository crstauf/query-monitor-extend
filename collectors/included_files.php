<?php
/**
 * Based on and inspired by khromov's "Query Monitor: Included files"
 * http://github.com/khromov/wp-query-monitor-included-files
 */

class QMX_Collector_IncludedFiles extends QM_Collector {

    public $id = 'qmx-included_files';

    public function name() {
        return __( 'Included files', 'query-monitor' );
    }

}

function register_qmx_collector_includedfiles( array $collectors, QueryMonitor $qm ) {
	$collectors['qmx-included_files'] = new QMX_Collector_IncludedFiles;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qmx_collector_includedfiles', 10, 2 );
