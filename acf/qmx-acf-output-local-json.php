<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * Sub-panel outputter for the Local JSON view.
 *
 * @property-read QMX_Collector_ACF $collector
 */
class QMX_Output_Html_ACF_LocalJSON extends QM_Output_Html {

	public function name() {
		return __( 'Advanced Custom Fields: Local JSON', 'query-monitor-extend' );
	}

	public function output() : void {
		/** @var QMX_Data_ACF */
		$data = $this->collector->get_data();
		$id   = 'qm-acf-local_json';
		$name = $this->name();

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

		if ( ! empty( $data->local_json['save'] ) ) {
			$directory = apply_filters( 'acf/settings/save_json', $data->local_json['save'] );
			echo '<tr>';
			echo '<th scope="row">Save</th>';
			echo '<td><code>acf/settings/save_json</code></td>';
			echo '<td colspan="2">' . QM_Output_Html::output_filename( self::remove_abspath( $directory ), $directory ) . '</td>';
			echo '</tr>';
		}

		if ( ! empty( $data->local_json['load'] ) ) {
			$i = 0;

			foreach ( $data->local_json['load'] as $path ) {
				echo '<tr>';

				if ( 0 === $i ) {
					echo '<th scope="row" rowspan="' . esc_attr( (string) count( $data->local_json['load'] ) ) . '">Load</th>';
					echo '<td rowspan="' . esc_attr( (string) count( $data->local_json['load'] ) ) . '"><code>acf/settings/load_json</code></td>';
				}

				echo '<td class="qm-num">' . esc_html( (string) $i ) . '</td>';
				echo '<td>' . QM_Output_Html::output_filename( self::remove_abspath( $path ), $path ) . '</td>';

				echo '</tr>';

				$i++;
			}
		}

		echo '</tbody>';
		echo '</table>';

		$this->output_field_groups();

		echo '</div>';
	}

	protected function output_field_groups() : void {
		$data = $this->collector->get_data();

		if ( empty( $data->local_json['groups'] ) ) {
			echo '<section class="qm-non-tabular"><div class="qm-notice"><p>No local JSON field groups found.</p></div></section>';
			return;
		}

		$groups = array();

		foreach ( $data->local_json['groups'] as $group ) {
			$groups[ $group['title'] ] = $group;
		}

		ksort( $groups, SORT_NATURAL );

		echo '<table>';
		echo '<caption><h2>Field Groups</caption>';
		echo '<thead>';
		echo '<tr>';
		echo '<th scope="col">Title</th>';
		echo '<th scope="col">Key</th>';
		echo '<th scope="col">File</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $groups as $group ) {
			printf(
				'<tr><th scope="row">%s</th><td>%s</td><td>%s</td></tr>',
				esc_html( $group['title'] ),
				esc_html( $group['key'] ),
				QM_Output_Html::output_filename( self::remove_abspath( $group['local_file'] ), $group['local_file'] )
			);
		}

		echo '</tbody>';
		printf( '<tfoot><tr><td colspan="3">Total: <span class="qm-items-number">%d</span></td></tr></tfoot>', count( $data->local_json['groups'] ) );
		echo '</table>';
	}

	public static function remove_abspath( string $path ) : string {
		return str_replace( ABSPATH, '', $path );
	}
}
