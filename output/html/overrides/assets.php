<?php
/**
 * Override Query Monitor's scripts and styles output for HTML pages.
 *
 * @package query-monitor-extend
 */

class QMX_Output_Html_Assets extends QM_Output_Html_Assets {

	public function output() {

		$data = $this->collector->get_data();

		if ( empty( $data['raw'] ) ) {
			return;
		}

		$position_labels = array(
			'missing' => __( 'Missing', 'query-monitor' ),
			'broken'  => __( 'Missing Dependencies', 'query-monitor' ),
			'header'  => __( 'Header', 'query-monitor' ),
			'footer'  => __( 'Footer', 'query-monitor' ),
		);

		$type_labels = array(
			'scripts' => array(
				/* translators: %s: Total number of enqueued scripts */
				'total'    => __( 'Total Enqueued Scripts: %s', 'query-monitor' ),
				'plural'   => __( 'Scripts', 'query-monitor' ),
			),
			'styles' => array(
				/* translators: %s: Total number of enqueued styles */
				'total'    => __( 'Total Enqueued Styles: %s', 'query-monitor' ),
				'plural'   => __( 'Styles', 'query-monitor' ),
			),
		);

		foreach ( $type_labels as $type => $type_label ) {

			$types = array();

			foreach ( $position_labels as $position => $label ) {
				if ( ! empty( $data[ $position ][ $type ] ) ) {
					$types[ $position ] = $label;
				}
			}

			$hosts = array(
				__( 'Other', 'query-monitor' ),
			);

			echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '-' . esc_attr( $type ) . '">';
			echo '<table>';
			echo '<caption>' . esc_html( $type_label['plural'] ) . '</caption>';
			echo '<thead>';
			echo '<tr>';
			echo '<th scope="col">' . esc_html__( 'Position', 'query-monitor' ) . '</th>';
			echo '<th scope="col" class="qm-filterable-column">';
			$args = array(
				'prepend' => array(
					// phpcs:ignore WordPress.VIP.ValidatedSanitizedInput
					'local' => wp_unslash( $_SERVER['HTTP_HOST'] ),
				),
			);
			echo $this->build_filter( $type . '-host', $hosts, __( 'Host', 'query-monitor' ), $args ); // WPCS: XSS ok.
			echo '</th>';
			echo '<th scope="col">' . esc_html__( 'Handle', 'query-monitor' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Source', 'query-monitor' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Dependencies', 'query-monitor' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Dependents', 'query-monitor' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Version', 'query-monitor' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Extras', 'query-monitor' ) . '</th>';
			echo '</tr>';
			echo '</thead>';

			echo '<tbody>';

			$total = 0;

			foreach ( $position_labels as $position => $label ) {
				if ( ! empty( $data[ $position ][ $type ] ) ) {
					$this->dependency_rows( $data[ $position ][ $type ], $data['raw'][ $type ], $label, $type );
					$total += count( $data[ $position ][ $type ] );
				}
			}

			echo '</tbody>';

			echo '<tfoot>';

			echo '<tr>';
			printf(
				'<td colspan="8">%1$s</td>',
				esc_html( sprintf(
					$type_label['total'],
					number_format_i18n( $total )
				) )
			);
			echo '</tr>';
			echo '</tfoot>';

			echo '</table>';
			echo '</div>';

		}

	}

	protected function dependency_row( _WP_Dependency $dependency, WP_Dependencies $dependencies, $type ) {

		if ( empty( $dependency->ver ) ) {
			$ver = '';
		} else {
			$ver = $dependency->ver;
		}

		list( $src, $host, $source, $local ) = $this->get_dependency_data( $dependency, $dependencies, $type );

		$dependents = $this->collector->get_dependents( $dependency, $dependencies );
		$deps = $dependency->deps;
		sort( $deps );

		foreach ( $deps as & $dep ) {
			if ( ! $dependencies->query( $dep ) ) {
				/* translators: %s: Script or style dependency name */
				$dep = sprintf( __( '%s (missing)', 'query-monitor' ), $dep );
			}
		}

		$this->type = $type;

		$highlight_deps       = array_map( array( $this, '_prefix_type' ), $deps );
		$highlight_dependents = array_map( array( $this, '_prefix_type' ), $dependents );

		echo '<td class="qm-nowrap qm-ltr">' . esc_html( $host ) . '</td>';
		echo '<td class="qm-nowrap qm-ltr">' . esc_html( $dependency->handle ) . '</td>';
		echo '<td class="qm-ltr">';
		if ( is_wp_error( $source ) ) {
			printf(
				 '<span class="qm-warn">%s</span>',
				esc_html( $src )
			);
		} else {
			echo esc_html( $src );
		}
		echo '</td>';
		echo '<td class="qm-ltr qm-highlighter" data-qm-highlight="' . esc_attr( implode( ' ', $highlight_deps ) ) . '">' . implode( ' ', array_map( 'esc_html', $deps ) ) . '</td>';
		echo '<td class="qm-ltr qm-highlighter" data-qm-highlight="' . esc_attr( implode( ' ', $highlight_dependents ) ) . '">' . implode( ' ', array_map( 'esc_html', $dependents ) ) . '</td>';
		echo '<td class="qm-ltr">' . esc_html( $ver ) . '</td>';
		echo '<td class="qm-has-inner">';
		echo '<table class="qm-inner">';
		foreach ( $dependency->extra as $k => $v ) {
			echo '<tr>';
			echo '<td>' . esc_html( $k ) . '</td>';
			echo '<td>';

			$display = QM_Util::display_variable( $v );
			echo ( is_string( $display ) )
				? substr( $display, 0, 20 ) . ( strlen( $display ) > 20 ? '...' : '' )
				: $display;

			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</td>';

	}

}

if ( $collector = QM_Collectors::get( 'assets' ) ) {
	$output['assets'] = new QMX_Output_Html_Assets( $collector );
}