<?php
/**
 * Based on and inspired by khromov's "Query Monitor: Included files"
 * http://github.com/khromov/wp-query-monitor-included-files
 */

class CSSLLC_QMX_Output_Html_IncludedFiles extends QM_Output_Html {

    public static $errors = 0;

    public function __construct( QM_Collector $collector ) {
        parent::__construct( $collector );
        add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 101 );
        add_filter( 'qm/output/title', array( $this, 'admin_title' ), 101 );
    }

    public function output() {
        $data = $this->collector->get_data();

        if (
            !array_key_exists( 'files', $data )
            || !is_array( $data['files'] )
            || !count( $data['files'] )
        )
            return;

        $php_errors = QM_Collectors::get( 'php_errors' )->get_data();
        if (
            array_key_exists( 'errors', $php_errors )
            && is_array( $php_errors['errors'] )
            && array_key_exists( 'warning', $php_errors['errors'] )
            && is_array( $php_errors['errors']['warning'] )
            && count( $php_errors['errors']['warning'] )
        )
            foreach ( $php_errors['errors']['warning'] as $error )
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

                    self::$errors++;

                    $include_file = substr( $error->message, $start, $length );
                    $component = QM_Util::get_file_component( $error->file )->name;

                    $selectors = array();
                    foreach ( array(
                        'including' => $error->file,
                        'included' => $include_file,
                    ) as $status => $file )
                        $selectors = array_merge( CSSLLC_QMX_Collector_IncludedFiles::get_selectors( $file, 'including' === $status ), $selectors );

                    $data['errors'][$include_file] = array(
                        'filesize' => filesize( $error->file ),
                        'component' => $component,
                        'including' => $error->file,
                        'including_line' => $error->line,
                        'selectors' => $selectors,
                    );
                }

        $selectors = array();
        foreach ( $data['files'] as $details )
            $selectors = array_merge( $details['selectors'], $selectors );
        if ( array_key_exists( 'errors', $data ) && is_array( $data['errors'] ) && count( $data['errors'] ) )
            foreach ( $data['errors'] as $details )
                $selectors = array_merge( $details['selectors'], $selectors );

        echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm">';

			echo '<table cellspacing="0" class="qm-sortable">' .
				'<thead>' .
					'<tr>' .
						'<th colspan="4">Included Files</th>' .
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
                            $this->build_filter( 'includedfilescomponent', array_keys( $data['components'] ) ) .
						'</th>' .
					'</tr>' .
				'</thead>' .
                '<tbody>';

                    if ( array_key_exists( 'errors', $data ) && is_array( $data['errors'] ) && count( $data['errors'] ) )
                        foreach ($data['errors'] as $path => $details) {
                            echo '<tr data-qm-includedfilespath="' . esc_attr( implode( ' ', array_keys( $details['selectors'] ) ) ) . '" data-qm-includedfilescomponent="' . esc_attr( $details['component'] ) . '" class="qm-warn">' .
                                '<td class="qm-num" data-qmsortweight="0"> </td>' .
                                '<td data-qmsortweight="' . esc_attr( str_replace( '.php', '', strtolower( $path ) ) ) . '">' .
                                    esc_html( $path ) .  '<br />' .
                                    '<span class="qm-info">&nbsp;' . esc_html( $details['including'] . ':' . $details['including_line'] ) .
                                '</td>' .
                                '<td class="qm-num qmx-includedfiles-filesize"> </td>' .
                                '<td class="qmx-includedfiles-component">' . esc_html( $details['component'] ) . '</td>' .
                            '</tr>';
                        }

                    $count = $filesize = 0;

					foreach ($data['files'] as $path => $details) {
                        $count++;
                        $filesize = $filesize + $details['filesize'];
                        echo '<tr data-qm-includedfilespath="' . esc_attr( implode( ' ', array_keys( $details['selectors'] ) ) ) . '" data-qm-includedfilescomponent="' . esc_attr( $details['component'] ) . '">' .
                            '<td class="qm-num" data-qmsortweight="' . esc_attr( $count ) . '">' . esc_html( $count ) . '</td>' .
                            '<td data-qmsortweight="' . esc_attr( str_replace( '.php', '', strtolower( $path ) ) ) . '">' .
                                ( strlen( trailingslashit( dirname( $path ) ) ) < strlen( ABSPATH )
                                ? esc_html( $path )
                                : '<abbr title="' . esc_attr( $path ) . '">./' . esc_html( str_replace( ABSPATH, '', $path) ) . '</abbr>') .
                            '</td>' .
                            '<td class="qm-num qmx-includedfiles-filesize" data-qmsortweight="' . esc_attr( $details['filesize'] ) . '">' . esc_html( number_format_i18n( $details['filesize'] / 1024, 2 ) ) . ' KB</td>' .
                            '<td>' . esc_html( $details['component'] ) . '</td>' .
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

            '</table>';

            wp_enqueue_script('jquery');
            ?>

            <script type="text/javascript">
                jQuery("#qm-included_files table").on('qm-filtered',function(ev,rows) {
                    var filesize = 0;
                    rows.each(function(row) {
                        filesize = filesize + parseInt( jQuery(row).find('td.qmx-includedfiles-filesize').attr('data-qmsortweight') );
                    });
                    jQuery("#qm-includedfiles table.qm-sortable tfoot .qm-items-filesize").text(filesize / 1024 + ' KB');
                });
            </script>

            <?php
        echo '</div>';
    }

    public function admin_title( array $title ) {
        $data = $this->collector->get_data();

        $title[] = sprintf(
            _x( '%s%s<small>F</small>', 'number of included files', 'query-monitor' ),
            array_key_exists( 'files', $data ) && is_array( $data['files'] ) ? count( $data['files'] ) : 0,
            0 !== self::$errors ? '/' . self::$errors : ''
        );

        return $title;
    }

    public function admin_menu( array $menu ) {
        $data = $this->collector->get_data();

        $add = array(
            'id'    => 'qm-included_files',
            'href'  => '#qm-included_files',
            'title' => sprintf(
                __( 'Included files (%s%s)', 'query-monitor' ),
                is_array( $data['files'] ) ? count( $data['files'] ) : 0,
                0 !== self::$errors ? ' / ' . self::$errors : ''
            )
        );

        if ( 0 !== self::$errors )
            $add['meta']['classname'] = 'qm-alert';

        $menu[] = $this->menu( $add );

        return $menu;
    }
}
function register_cssllc_qmx_output_html_includedfiles( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'included_files' ) )
		$output['included_files'] = new CSSLLC_QMX_Output_Html_IncludedFiles( $collector );
	return $output;
}
