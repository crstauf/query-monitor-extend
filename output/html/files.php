<?php
/**
 * Files output for HTML pages.
 *
 * @package query-monitor-extend
 */

class QMX_Output_Html_Files extends QMX_Output_Html {

	public function __construct( QMX_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/title',       array( &$this, 'admin_title' ), 40 );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu'  ), 60 );
	}

	public function output() {
		$data = $this->collector->get_data();

		echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';

			if ( !empty( $data['files'] ) ) {
				$files_with_errors = 0;
				$path_components = $components = array();

				$largest_file = array(
					'path' => null,
					'size' => 0
				);

				foreach ( $data['files'] as &$file ) {
					$file['_path_components'] = array();
					foreach ( array_filter( explode( '/', str_replace( ABSPATH, '', dirname( $file['path'] ) ) ) ) as $path_component ) {
						$path_components[$path_component]
							= $file['_path_components'][$path_component]
							= 1;
						foreach ( explode( '-', $path_component ) as $smaller_path_component )
							$path_components[$smaller_path_component]
								= $file['_path_components'][$smaller_path_component]
								= 1;
					}
					$components[$file['component']->name] = 1;
				}

				echo '<table class="qm-sortable">';
					echo '<caption class="qm-screen-reader-text">' . esc_html( $this->collector->name() ) . '</caption>';
					echo '<thead>';
						echo '<tr>';

							echo '<th scope="col" class="qm-num qm-sorted-asc qm-sortable-column">';
								echo $this->build_sorter( __( '', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-filterable-column">';
								echo $this->build_filter( 'path', array_map( 'esc_attr', array_keys( $path_components ) ), __( 'Path', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-ltr qm-sortable-column">';
								echo $this->build_sorter( __( 'Filesize', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-filterable-column">';
								echo $this->build_filter( 'component', array_map( 'esc_attr', array_keys( $components ) ), __( 'Component', 'query-monitor-extend' ) );
							echo '</th>';

						echo '</tr>';
					echo '</thead>';

					echo '<tbody>';

						foreach ( $data['files'] as $i => $file ) {

							if ( filesize( $file['path'] ) > $largest_file['size'] )
								$largest_file = array(
									'path' => $file['path'],
									'size' => filesize( $file['path'] ),
								);

							if ( !empty( $file['has_error'] ) )
								$files_with_errors++;

							echo '<tr ' .
								'data-qm-component="' . esc_attr( $file['component']->name ) . '"' .
								'data-qm-path="' . esc_attr( implode( ' ', array_keys( $file['_path_components'] ) ) ) . '"' .
								( !empty( $file['has_error'] ) ? ' class="qm-warn"' : '' ) .
							'>';
								echo '<td class="qm-num">' . ( $i + 1 ) . '</td>';
								echo '<td title="' . esc_attr( $file['path'] ) . '">' . esc_html( str_replace( ABSPATH, '/', $file['path'] ) ) . '</td>';
								echo '<td data-qm-sort-weight="' . filesize( $file['path'] ) . '">' . $this->human_file_size( filesize( $file['path'] ) ) . '</td>';
								echo '<td>' . esc_html( $file['component']->name ) . '</td>';
							echo '</tr>';
						}

					echo '</tbody>';
					echo '<tfoot>';

						echo '<tr class="qm-items-shown qm-hide">';
							echo '<td colspan="4">';
							printf(
								esc_html__( 'Files in filter: %s', 'query-monitor-extend' ),
								'<span class="qm-items-number">' . esc_html( number_format_i18n( count( $data['files']) ) ) . '</span>'
							);
							echo '</td>';
						echo '</tr>';

						echo '<tr>';
							echo '<td colspan="2">' .
								'Total: <span class="qm-items-number">' . esc_html( number_format_i18n( count( $data['files'] ) ) ) . '</span>' .
								(
									!empty( $files_with_errors )
									? ', With error(s): <span>' . esc_html( number_format_i18n( $files_with_errors ) ) . '</span>'
									: ''
								) .
							'</td>';
							echo '<td>Largest: <abbr title="' . esc_attr( $largest_file['path'] ) . '">' . $this->human_file_size( $largest_file['size'] ) . '</td>';
							echo '<td>Components: ' . count( $components ) . '</td>';
						echo '</tr>';

					echo '</tfoot>';
				echo '</table>';

				echo '<style type="text/css">.qm-hide-path { display: none; }</style>';

			} else {

				echo '<div class="qm-none">';
				echo '<p>' . esc_html__( 'None', 'query-monitor' ) . '</p>';
				echo '</div>';

			}

		echo '</div>';
	}

	public function admin_title( array $title ) {
		$data = $this->collector->get_data();

		if ( !empty( $data['files'] ) ) {
			$_title = sprintf( esc_html_x( '%s F', 'Files count', 'query-monitor-extend' ), number_format_i18n( count( $data['files'] ) ) );
			$_title = preg_replace( '#\s?([^0-9,\.]+)#', '<small>$1</small>', $_title );
			$title[] = $_title;
		}

		return $title;

	}

	public function panel_menu( array $menu ) {

		$menu['files'] = $this->menu( array(
			'title' => esc_html__( 'Files', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-files',
		) );

		return $menu;

	}

	private function human_file_size( $bytes ) {
		$filesize_units = 'BKMGTP';
		$factor = ( int ) floor( ( strlen( $bytes ) - 1 ) / 3 );
		return sprintf( "%.2f", $bytes / pow( 1024, $factor ) ) . @$filesize_units[$factor];
	}

}

function register_qmx_output_html_files( array $output ) {
	if ( $collector = QMX_Collectors::get( 'files' ) ) {
		$output['files'] = new QMX_Output_Html_Files( $collector );
	}
	return $output;
}

add_filter( 'qmx/outputter/html', 'register_qmx_output_html_files', 70 );
