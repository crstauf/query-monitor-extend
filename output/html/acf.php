<?php
/**
 * ACF output for HTML pages.
 *
 * @package query-monitor-extend
 */

class QMX_Output_Html_ACF extends QMX_Output_Html {

	public function __construct( QMX_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function name() {
		return __( 'Advanced Custom Fields', 'query-monitor-extend' );
	}

	public function output() {
		$data = $this->collector->get_data();

		$this->before_tabular_output();

		echo '<thead>';
			echo '<tr>';

				echo '<th scope="col" class="qm-sorted-asc qm-sortable-column" role="columnheader" aria-sort="ascending">';
				echo $this->build_sorter( '#' ); // WPCS: XSS ok;
				echo '</th>';

				echo '<th scope="col">' . esc_html__( 'Field name', 'query-monitor' ) . '</th>';
				echo '<th scope="col">' . esc_html__( 'Post ID', 'query-monitor' ) . '</th>';
				echo '<th scope="col">' . esc_html__( 'Caller', 'query-monitor' ) . '</th>';

			echo '</tr>';
		echo '</thead>';

		echo '<tbody>';

			foreach ( $data['fields'] as $row_num => $row ) {
				echo '<tr>';
					echo '<th scope="row" class="qm-row-num qm-num">' . esc_html( $row_num + 1 ) . '</th>';
					echo '<td class="qm-ltr">' . esc_html( $row['field']['name'] ) . '</td>';
					echo '<td class="qm-ltr">' . esc_html( $row['post_id'] ) . '</td>';

					echo '<td class="qm-row-caller qm-ltr qm-has-toggle qm-nowrap">';
					$this->row_caller( $row );
					echo '</td>';

				echo '</tr>';
			}

		echo '</tbody>';

		$this->after_tabular_output();
	}

	protected function row_caller( array $row ) {
		$trace          = $row['trace']->ignore( 1 );
		$filtered_trace = $trace->get_display_trace();
		$caller_name    = self::output_filename( $filtered_trace[0]['function'] . '()', $filtered_trace[0]['file'], $filtered_trace[0]['line'] );
		$stack          = array();
		array_shift( $filtered_trace );

		foreach ( $filtered_trace as $item ) {
			$stack[] = self::output_filename( $item['display'], $item['file'], $item['line'] );
		}

		if ( ! empty( $stack ) ) {
			echo self::build_toggler(); // WPCS: XSS ok;
		}

		echo '<ol>';
		echo "<li>{$caller_name}</li>"; // WPCS: XSS ok.

		if ( ! empty( $stack ) ) {
			echo '<div class="qm-toggled"><li>' . implode( '</li><li>', $stack ) . '</li></div>'; // WPCS: XSS ok.
		}

		echo '</ol>';
	}

	protected function output_local_json() {
		$data = $this->collector->get_data();
		$data = $data['local_json'];

		$header_row = '<th scope="row" rowspan="' . esc_attr( count( $data['load'] ) ) . '">load_json</th>';

		echo '<section>';
			echo '<h3>Local JSON <a href="https://www.advancedcustomfields.com/resources/local-json/" target="_blank" rel="noopener noreferrer" class="qm-external-link">Help</a></h3>';
			echo '<table>';
				echo '<tbody>';
					echo '<tr>';
						echo '<th scope="row">save_json</th>';
						echo '<td colspan="2">';

							if ( empty( $data['save'] ) ) {
								echo '<span class="qm-info">None</span>';
							} else {
								echo '<code>' . esc_html( $data['save'] ) . '</code>';
							}

						echo '</td>';
					echo '</tr>';

						if ( empty( $data['load'] ) )
							echo '<tr colspan="2">' . $header_row . '<td class="qm-info">None</td></tr>';

						foreach ( $data['load'] as $i => $path ) {
							echo '<tr>';

							if ( 0 === $i )
								echo $header_row;

							echo '<td class="qm-num">' . esc_html( absint( $i ) ) . '</td>';

							echo '<td>';
								echo '<code>' . esc_html( $path ) . '</code>';
							echo '</td>';

							echo '</tr>';
						}

				echo '</tbody>';
			echo '</table>';
		echo '</section>';
	}

	protected function output_field_groups() {
		$data = $this->collector->get_data();
		$data = $data['field_groups'];

		echo '<section>';
			echo '<h3>Field Groups</h3>';
			echo '<table>';
				echo '<thead>';
					echo '<th scope="col">Post ID</th>';
					echo '<th scope="col">Title</th>';
					echo '<th scope="col">Key</th>';
					// echo '<th scope="col">'
				echo '<tbody>';
		echo '</section>';
	}

	public function panel_menu( array $menu ) {

		$menu['qm-acf'] = $this->menu( array(
			'title' => esc_html__( 'Advanced Custom Fields', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-acf',
		) );

		return $menu;

	}

}

function register_qmx_output_html_acf( array $output ) {
	if ( $collector = QMX_Collectors::get( 'acf' ) ) {
		$output['acf'] = new QMX_Output_Html_ACF( $collector );
	}
	return $output;
}

add_filter( 'qmx/outputter/html', 'register_qmx_output_html_acf', 70 );