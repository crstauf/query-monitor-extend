<?php
/**
 * Image sizes output for HTML pages.
 *
 * @package query-monitor-extend
 */

class QMX_Output_Html_Image_Sizes extends QMX_Output_Html {

	public function __construct( QMX_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function output() {
		$data = $this->collector->get_data();

		echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';

			if ( !empty( $data['sizes'] ) ) {
				echo '<table class="qm-sortable">';
					echo '<caption class="qm-screen-reader-text">' . esc_html( $this->collector->name() ) . '</caption>';
					echo '<thead>';
						echo '<tr>';

							echo '<th scope="col" class="qm-num qm-sorted-asc qm-sortable-column">';
								echo $this->build_sorter( __( '', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-sortable-column">';
								echo $this->build_sorter( __( 'ID', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-num qm-sortable-column">';
								echo $this->build_sorter( __( 'Width', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-num qm-sortable-column">';
								echo $this->build_sorter( __( 'Height', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-num qm-sortable-column">';
								echo $this->build_sorter( __( 'Ratio', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-num">';
								echo __( 'Cropped', 'query-monitor-extend' );
							echo '</th>';

							echo '<th scope="col" class="qm-sortable-column">';
								echo $this->build_sorter( __( 'Source', 'query-monitor-extend' ) );
							echo '</th>';

						echo '</tr>';
					echo '</thead>';

					echo '<tbody>';

						$sources = array();

						foreach ( $data['sizes'] as $id => $row ) {
							$ratio = array( $row['width'], $row['height'] );

							if (
								    !empty( $row['width'] )
								&& !empty( $row['height'] )
							)
								$ratio = array( $row['width'] / $row['_gcd'], $row['height'] / $row['_gcd'] );

							if ( $ratio === array( $row['width'], $row['height'] ) )
								$ratio = array( '&mdash;' );

							echo '<tr data-qmx-image-size-width="' . esc_attr( $row['width'] ) . '" data-qmx-image-size-height="' . esc_attr( $row['height'] ) . '" data-qmx-image-size-ratio="' . esc_attr( $row['ratio'] ) . '">';
								echo '<td class="qm-num">' . esc_html( $row['num']    ) . '</td>';
								echo '<td class="qm-ltr">' . esc_html( $id            ) . '</td>';
								echo '<td class="qm-num" data-qmx-image-size-width="'  . esc_attr( $row['width']  ) . '">' . esc_html( $row['width']  ) . '</td>';
								echo '<td class="qm-num" data-qmx-image-size-height="' . esc_attr( $row['height'] ) . '">' . esc_html( $row['height'] ) . '</td>';
								echo '<td class="qm-num" data-qmx-image-size-ratio="'  . esc_attr( $row['ratio']  ) . '" data-qm-sort-weight="' . esc_attr( $row['ratio'] ) . '">' . esc_html( implode( ':', $ratio )  ) . '</td>';
								echo '<td class="qm-num qm-true">' . ( $row['crop'] ? '<span class="dashicons dashicons-yes"></span>' : '' ) . '</td>';
								echo '<td class="qm-ltr">' . esc_html( $row['source'] ) . '</td>';
							echo '</tr>';

							array_key_exists( $row['source'], $sources ) ? $sources[$row['source']]++ : $sources[$row['source']] = 1;
						}

					echo '</tbody>';
					echo '<tfoot>';

						if ( !empty( $sources ) )
							$sources = array_map( function( $k, $v ) { return ucwords( $k ) . ': ' . $v; }, array_keys( $sources ), $sources );

						echo '<tr>';
							echo '<td class="qm-num">Total: <span class="qm-items-number">' . esc_html( number_format_i18n( count( $data['sizes'] ) ) ) . '</span></td>';
							echo '<td>&nbsp;</td>';
							echo '<td colspan="2">Duplicates: <span class="qm-items-number">' . esc_html( number_format_i18n( array_sum( $data['_duplicates']['dimensions'] ) ) ) . '</span></td>';
							echo '<td colspan="2">Duplicates: <span class="qm-items-number">' . esc_html( number_format_i18n( array_sum( $data['_duplicates']['ratios'] ) ) ) . '</span></td>';
							echo '<td>' . implode( ', ', $sources ) . '</td>';
						echo '</tr>';

					echo '</tfoot>';
				echo '</table>';

			} else {

				echo '<div class="qm-none">';
				echo '<p>' . esc_html__( 'None', 'query-monitor' ) . '</p>';
				echo '</div>';

			}

		echo '</div>';
	}

	public function panel_menu( array $menu ) {

		$menu['image_sizes'] = $this->menu( array(
			'title' => esc_html__( 'Image Sizes', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-image_sizes',
		) );

		return $menu;

	}

}

function register_qmx_output_html_image_sizes( array $output ) {
	if ( $collector = QMX_Collectors::get( 'image_sizes' ) ) {
		$output['image_sizes'] = new QMX_Output_Html_Image_Sizes( $collector );
	}
	return $output;
}

add_filter( 'qmx/outputter/html', 'register_qmx_output_html_image_sizes', 70 );
