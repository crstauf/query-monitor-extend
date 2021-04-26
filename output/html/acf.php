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

		if ( empty( $data['fields'] ) ) {
			$this->before_non_tabular_output();
			echo '<div class="qm-notice"><p>No Advanced Custom Fields.</p></div>';
			$this->after_non_tabular_output();
			return;
		}

		echo '<style>.qm-hide-acf-field, .qm-hide-acf-post, .qm-hide-acf-group, .qm-hide-acf-caller { display: none !important; }</style>';

		natsort( $data['field_keys'] );
		natsort( $data['post_ids'] );
		natsort( $data['field_groups'] );
		natsort( $data['callers'] );

		$this->before_tabular_output();

		echo '<thead>';

			echo '<tr>';

				echo '<th scope="col" class="qm-sorted-asc qm-sortable-column" role="columnheader" aria-sort="ascending">';
				echo $this->build_sorter( '#' );
				echo '</th>';

				echo '<th scope="col" class="qm-filterable-column">';
				echo $this->build_filter( 'acf-field', $data['field_keys'], __( 'Field', 'query-monitor' ), array(
					'prepend' => array(
						'qmx-acf-no-field' => 'Not Found',
					),
				) );
				echo '</th>';

				echo '<th scope="col" class="qm-filterable-column">';
				echo $this->build_filter( 'acf-post', $data['post_ids'], __( 'Post ID', 'query-monitor' ) );
				echo '</th>';

				echo '<th scope="col" class="qm-filterable-column">';
				echo $this->build_filter( 'acf-group', $data['field_groups'], __( 'Group', 'query-monitor' ) );
				echo '</th>';

				echo '<th scope="col" class="qm-filterable-column">';
				echo $this->build_filter( 'acf-caller', array_keys( $data['callers'] ), __( 'Caller', 'query-monitor' ) );
				echo '</th>';

			echo '</tr>';

		echo '</thead>';

		echo '<tbody>';

			foreach ( $data['fields'] as $row_num => $row ) {
				$row_attr = array();

				if ( !$row['exists'] )
					$row_attr['class'] = 'qm-warn';

				$row_attr['data-qm-acf-field']  = $row['field']['name'] . ' ' . $row['field']['key'];
				$row_attr['data-qm-acf-post']   = $row['post_id'];
				$row_attr['data-qm-acf-caller'] = $row['caller']['function'] . '()';
				$row_attr['data-qm-acf-group']  = 'qmx-acf-no-group';

				if ( empty( $row['field']['key'] ) )
					$row_attr['data-qm-acf-field'] .= ' qmx-acf-no-field';

				if ( !empty( $row['group'] ) )
					$row_attr['data-qm-acf-group'] = $row['group']['key'];

				$attr = '';

				foreach ( $row_attr as $a => $v )
					$attr .= ' ' . $a . '="' . esc_attr( $v ) . '"';

				echo '<tr' . $attr . '>';

					# Number
					echo '<th scope="row" class="qm-row-num qm-num">' . esc_html( $row_num + 1 ) . '</th>';

					# Field name
					echo '<td class="qm-ltr qm-has-toggle qm-nowrap">';

						echo esc_html( $row['field']['name'] );

						if ( $row['exists'] ) {
							$parent = $row['field']['parent'];

							if ( !empty( $row['group'] ) )
								$parent = $row['group']['key'];

							echo self::build_toggler();
							echo '<div class="qm-toggled qm-supplemental qm-info">';
								echo esc_html( 'Key: ' . $row['field']['key'] );
								echo '<br />' . esc_html( 'Parent: ' . $parent );
							echo '</div>';
						}

					echo '</td>';

					# Post ID
					echo '<td class="qm-ltr">' . esc_html( $row['post_id'] ) . '</td>';

					# Field group
					echo '<td class="qm-ltr">';
					$this->output_column_field_group( $row );
					echo '</td>';

					# Caller
					echo '<td class="qm-row-caller qm-ltr qm-has-toggle qm-nowrap">';
					$this->output_column_caller( $row );
					echo '</td>';

				echo '</tr>';
			}

		echo '</tbody>';

		echo '</table>';
		echo '</div>';

		$id = 'qm-acf-local_json';
		$name = 'Advanced Custom Fields: Local JSON';

		printf(
			'<div class="qm qm-concerns" id="%1$s" role="tabpanel" aria-labelledby="%1$s-caption" tabindex="-1">',
			esc_attr( $id )
		);

		echo '<table class="qm-sortable">';

			printf(
				'<caption><h2 id="%1$s-caption">%2$s<br />%3$s</h2></caption>',
				esc_attr( $id ),
				esc_html( $name ),
				'<a href="https://www.advancedcustomfields.com/resources/local-json/" rel="noopener noreferrer" class="qm-external-link">Documentation</a>'
			);

			echo '<thead>';
				echo '<tr>';
					echo '<th scope="col">Action</th>';
					echo '<th scope="col">Hook</th>';
					echo '<th scope="col" colspan="2">Paths</th>';
				echo '</tr>';
			echo '</thead>';

			echo '<tbody>';

			if ( !empty( $data['local_json']['save'] ) ) {
				echo '<tr>';
					echo '<th scope="row">Save</th>';
					echo '<td><code>acf/settings/save_json</code></td>';
					echo '<td colspan="2"><code>' . esc_html( $this->remove_abspath( $data['local_json']['save'] ) ) . '</code></td>';
				echo '</tr>';
			}

			if ( !empty( $data['local_json']['load'] ) ) {
				$i = 0;

				foreach ( $data['local_json']['load'] as $path ) {
					echo '<tr>';

						if ( 0 === $i ) {
							echo '<th scope="row" rowspan="' . esc_attr( count( $data['local_json']['load'] ) ) . '">Load</th>';
							echo '<td rowspan="' . esc_attr( count( $data['local_json']['load'] ) ) . '"><code>acf/settings/load_json</code></td>';
						}

						echo '<td>' . esc_html( $i ) . '</td>';
						echo '<td><code>' . esc_html( $this->remove_abspath( $path ) ) . '</code></td>';

					echo '</tr>';

					$i++;
				}
			}

			echo '</tbody>';
		echo '</table>';

		echo '</section>';

		echo '</div>';
		echo '</div>';

		$this->output_concerns();
	}

	protected function output_column_field_group( array $row ) {
		$group = $row['group'];

		if ( empty( $group ) )
			return;

		echo $group['title'];
	}

	protected function output_column_caller( array $row ) {
		$trace          = $row['trace'];
		$filtered_trace = $trace->get_display_trace();
		$caller_name    = self::output_filename( $row['caller']['function'] . '()', $row['caller']['file'], $row['caller']['line'] );
		$stack          = array();
		array_shift( $filtered_trace );

		foreach ( $filtered_trace as $item ) {
			$stack[] = self::output_filename( $item['display'], $item['file'], $item['line'] );
		}

		echo self::build_toggler();

		echo '<ol>';
		echo "<li>{$caller_name}</li>";
		echo '<div class="qm-toggled"><li>' . implode( '</li><li>', $stack ) . '</li></div>';
		echo '</ol>';
	}

	public function remove_abspath( string $path ) : string {
		return str_replace( ABSPATH, '', $path );
	}

	public function panel_menu( array $menu ) {

		$menu['qm-acf'] = $this->menu( array(
			'title' => esc_html__( 'Advanced Custom Fields', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-acf',
		) );

		$menu['qm-acf']['children']['local_json'] = array(
			'title' => esc_html__( 'Local JSON', 'query-monitor-extend' ),
			'href'  => '#qm-acf-local_json',
			'id'    => 'query-monitor-extend-acf-local_json',
		);

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