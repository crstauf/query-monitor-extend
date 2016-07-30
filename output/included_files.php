<?php
/**
 * Based on and inspired by khromov's "Query Monitor: Included files"
 * http://github.com/khromov/wp-query-monitor-included-files
 */

class QMX_Output_Html_IncludedFiles extends QM_Output_Html {

    var $data = array();

    public function __construct( QM_Collector $collector ) {
        parent::__construct( $collector );
        add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 101 );
        add_filter( 'qm/output/title', array( $this, 'admin_title' ), 101 );
    }

    public function hide_core_files() {
        return defined( 'QMX_HIDE_CORE_FILES' ) && QMX_HIDE_CORE_FILES;
    }

    public function collect_data() {
        $this->data['components'] = array();

        foreach ( get_included_files() as $file ) {
            $component = QM_Util::get_file_component( $file )->name;
            if ( $this->hide_core_files() && 'Core' === $component )
                continue;

            if (array_key_exists($component,$this->data['components']))
                $this->data['components'][$component]++;
            else
                $this->data['components'][$component] = 1;

            $this->data['files'][$file] = array(
                'component' => $component,
                'filesize' => filesize( $file ),
                'selectors' => $this->get_selectors( $file ),
            );
        }

        $php_errors = QM_Collectors::get( 'php_errors' )->get_data();

        if (
            array_key_exists( 'errors', $php_errors )
            && is_array( $php_errors['errors'] )
            && array_key_exists( 'warning', $php_errors['errors'] )
            && is_array( $php_errors['errors']['warning'] )
            && count( $php_errors['errors']['warning'] )
        )
            foreach ( $php_errors['errors']['warning'] as $error ) {

                $component = QM_Util::get_file_component( $error->file )->name;
                if ( $this->hide_core_files() && 'Core' === $component )
                    continue;

                if (false !== stripos( $error->message, 'No such file or directory' ) ) {
                    foreach ( array(
                        'include(',
                        'include_once(',
                    ) as $function ) {
                        $start = stripos( $error->message, $function );
                        if ( false !== $start ) break;
                    }

                    if ( false === $start )
                        continue;

                    $start = $start + strlen( $function );
                    $length = strpos( $error->message, ')', $start ) - $start;

                    if ( false === $length )
                        continue;

                    $include_file = substr( $error->message, $start, $length );

                    $selectors = array();
                    foreach ( array(
                        'including' => $error->file,
                        'included'  => $include_file,
                    ) as $status => $file )
                        $selectors = array_merge(
                            $this->get_selectors( $file, 'including' === $status ),
                            $selectors
                        );

                    $this->data['errors'][$include_file] = array(
                        'filesize'       => filesize( $error->file ),
                        'component'      => $component,
                        'including'      => $error->file,
                        'including_line' => $error->line,
                        'selectors'      => $selectors,
                    );
                }
            }
    }

    public function get_selectors( $file, $above_or_root = true ) {
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

    public function output() {
        $this->collect_data();

        if (
            (
                !array_key_exists( 'files', $this->data )
                || !is_array( $this->data['files'] )
                || !count( $this->data['files'] )
            ) && (
                !array_key_exists( 'errors', $this->data )
                || !is_array( $this->data['errors'] )
                || !count( $this->data['errors'] )
            )
        )
            return;

        $selectors = array();

        foreach ( array( 'files', 'errors' ) as $status )
            if (
                array_key_exists( $status, $this->data )
                && is_array( $this->data[$status] )
                && count( $this->data[$status] )
                && array_key_exists( 'selectors', $this->data[$status] )
                && is_array( $this->data[$status]['selectors'] )
                && count( $this->data[$status]['selectors'] )
            )
                foreach ( $this->data[$status] as $details )
                    $selectors = array_merge( $details['selectors'], $selectors );

        echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm">';

			echo '<table cellspacing="0" class="qm-sortable">' .
				'<thead>' .
					'<tr>' .
						'<th colspan="4">Included Files' .
                            '<label class="qmx-filter-hide' .
                                (
                                    $this->hide_core_files()
                                    ? ' qm-info" title="QMX_HIDE_CORE_FILES constant is true'
                                    : ''
                                ) . '" style="float: right;"><input type="checkbox" data-filter="includedfilescomponent" value="Core"' .
                                (
                                    $this->hide_core_files()
                                    ? ' checked="checked" disabled="disabled"'
                                    : ''
                                ) . ' /> Hide core files</label>' .
                        '</th>' .
					'</tr>' .
					'<tr>' .
                        '<th class="qm-num qm-sorted-asc"><br />' . $this->build_sorter() . '</th>' .
						'<th>File' .
                            '<span class="flex">' .
                                '<span>' .
        							str_replace(
        								'class="qm-sort-controls"',
        								'class="qm-sort-controls" style="text-align: left !important;"',
        								$this->build_sorter()
        							) .
                                '</span>' .
                                '<span>' .
                                    $this->build_filter( 'includedfilespath', array_keys( $selectors ) ) .
                                '</span>' .
                            '</span>' .
                        '</th>' .
						'<th class="qm-num qmx-includedfiles-filesize">Filesize' . $this->build_sorter() . '</th>' .
						'<th class="qmx-includedfiles-component">Component' .
                            $this->build_filter( 'includedfilescomponent', array_keys( $this->data['components'] ) ) .
						'</th>' .
					'</tr>' .
				'</thead>' .
                '<tbody>';

                    $count = $filesize = 0;

                    foreach ( array( 'errors', 'files' ) as $status )
                        if (
                            array_key_exists( $status, $this->data )
                            && is_array( $this->data[$status] )
                            && count( $this->data[$status] )
                        )
                            foreach ( $this->data[$status] as $path => $details ) {
                                if ( 'files' === $status ) {
                                    $count++;
                                    $filesize = $filesize + intval( $details['filesize'] );
                                }
                                echo '<tr ' .
                                        'data-qm-includedfilespath="' . esc_attr( implode( ' ', array_keys( $details['selectors'] ) ) ) . '" ' .
                                        'data-qm-includedfilescomponent="' . esc_attr( $details['component'] ) . '"' .
                                        ( 'errors' === $status ? ' class="qm-warn"' : '' ) .
                                    '>' .
                                        '<td class="qm-num" data-qm-sort-weight="' . ( 'errors' === $status ? 0 : $count ) . '">' .
                                            ( 'errors' === $status ? ' ' : $count ) .
                                        '</td>' .
                                        '<td data-qm-sort-weight="' . esc_attr( str_replace( '.php', '', strtolower( $path ) ) ) . '">' .
                                            (
                                                'errors' === $status
                                                    || strlen( trailingslashit( dirname( $path ) ) ) < strlen( ABSPATH )
                                                ? esc_html( $path ) .
                                                    (
                                                        array_key_exists( 'including', $details)
                                                        && array_key_exists( 'including_line', $details )
                                                        ? '<br /><span class="qm-info">&nbsp;' . esc_html( $details['including'] . ':' . $details['including_line'] )
                                                        : ''
                                                    )
                                                : '<abbr title="' . esc_attr( $path ) . '">' .
                                                    esc_html( false === $this->get_relative_path( $path ) ? $path : './' . $this->get_relative_path( $path ) ) .
                                                '</abbr>'
                                            ) .
                                        '</td>' .
                                        '<td ' .
                                            'class="qm-num qmx-includedfiles-filesize" ' .
                                            'data-qm-sort-weight="' .
                                                (
                                                    'errors' === $status
                                                    ? '0'
                                                    : esc_attr( $details['filesize'] )
                                                ) .
                                            '"' .
                                        '>' .
                                            (
                                                'errors' === $status
                                                ? ' '
                                                : esc_html( number_format_i18n( $details['filesize'] / 1024, 2 ) ) . ' KB'
                                            ) .
                                        '</td>' .
                                        '<td class="qmx-includedfiles-component">' . esc_html( $details['component'] ) . '</td>' .
                                '</tr>';
                            }

				echo '</tbody>' .
				'<tfoot>' .
                    '<tr class="qm-items-shown qm-hide">' .
                        '<td colspan="4">Files in filter: <span class="qm-items-number">0</span></td>' .
                        '<td colspan="2" class="qm-hide">Total in filter: <span class="qm-items-filesize">0<span></td>' .
                    '</tr>' .
					'<tr>' .
                        '<td colspan="2">Total files: ' . $count . '</td>' .
                        '<td colspan="2">Total: ' . number_format_i18n( $filesize / 1024, 2) . ' KB Average: ' . number_format_i18n( ( $filesize / $count ) / 1024, 2) . ' KB</td>' .
                    '</tr>' .
				'</tfoot>' .

            '</table>' .
        '</div>';
    }

    public function get_relative_path( $path ) {
        if ( false === stripos( $path, ABSPATH ) )
            return false;

        return str_replace( ABSPATH, '', $path );
    }

    public function admin_title( array $title ) {

        $title[] = sprintf(
            _x( '%s%s<small>F</small>', 'number of included files', 'query-monitor' ),
            (
                array_key_exists( 'files', $this->data ) && is_array( $this->data['files'] )
                ? count( $this->data['files'] )
                : 0
            ),
            (
                array_key_exists( 'errors', $this->data )
                    && is_array( $this->data['errors'] )
                    && 0 !== count( $this->data['errors'] )
                ? '/' . count( $this->data['errors'] )
                : ''
            )
        );

        return $title;
    }

    public function admin_menu( array $menu ) {

        $add = array(
            'title' => sprintf(
                __( 'Included files (%s%s)', 'query-monitor' ),
                (
                    is_array( $this->data['files'] )
                    ? count( $this->data['files'] )
                    : 0
                ),
                (
                    array_key_exists( 'errors', $this->data )
                        && is_array( $this->data['errors'] )
                        && 0 !== count( $this->data['errors'] )
                    ? '/' . count( $this->data['errors'] )
                    : ''
                )
            )
        );

        if (
            array_key_exists( 'errors', $this->data )
            && is_array( $this->data['errors'] )
            && 0 !== count( $this->data['errors'] )
        )
            $add['meta']['classname'] = 'qm-alert';

        $menu[] = $this->menu( $add );

        return $menu;
    }
}
function register_qmx_output_html_includedfiles( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'qmx-included_files' ) )
		$output['qmx-included_files'] = new QMX_Output_Html_IncludedFiles( $collector );
	return $output;
}
