<?php
/**
 * Based on and inspired by khromov's "Query Monitor: Included files"
 * http://github.com/khromov/wp-query-monitor-included-files
 */

class CSSLLC_QMX_Collector_IncludedFiles extends QM_Collector {

    public $id = 'included_files';

    public function name() {
        return __( 'Included files', 'query-monitor' );
    }

    public function process() {
        $this->data['components'] = array();
        foreach ( get_included_files() as $file ) {
            $component = QM_Util::get_file_component( $file )->name;

            if (array_key_exists($component,$this->data['components']))
                $this->data['components'][$component]++;
            else
                $this->data['components'][$component] = 1;

            $this->data['files'][$file] = array(
                'component' => $component,
                'filesize' => filesize( $file ),
                'selectors' => self::get_selectors( $file ),
            );
        }
    }

    public static function get_selectors( $file, $above_or_root = true ) {
        $path = dirname( str_replace( ABSPATH, '', $file ) );
        $selectors = array();

        if ( $above_or_root && strlen( trailingslashit( dirname( $file ) ) ) < strlen( ABSPATH ) ) {
            $selectors['__above-install'] = 1;
            $path = $file;
        } else {
            if ( $above_or_root && ABSPATH === trailingslashit( dirname( $file ) ) )
                $selectors['__root'] = 1;
            $path = str_replace( DIRECTORY_SEPARATOR, '-', $path );
            $path = str_replace( '_', '-', $path );
            $path = explode( '-', $path );

            if ( is_array( $path ) && count( $path ) )
                foreach ( $path as $piece ) {
                    if ( in_array( $piece, array( '', '.', ' ' ) ) )
                        continue;
                    $selectors[$piece] = 1;
                }
        }

        return $selectors;
    }

    /**
    * Adapted from:
    * http://stackoverflow.com/a/2510459
    */
    private function format_bytes_to_kb( $bytes, $precision = 2 ) {
        $bytes = max( $bytes, 0 );
        $bytes /= pow( 1000, 1 );

        return round( $bytes, $precision );
    }
}

function register_cssllc_qmx_collector_includedfiles( array $collectors, QueryMonitor $qm ) {
	$collectors['included_files'] = new CSSLLC_QMX_Collector_IncludedFiles;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_cssllc_qmx_collector_includedfiles', 10, 2 );
