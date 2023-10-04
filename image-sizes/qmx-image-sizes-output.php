<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @property-read QMX_Collector_Image_Sizes $collector
 */
class QMX_Output_Html_Image_Sizes extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function name() {
		return __( 'Image Sizes', 'query-monitor-extend' );
	}

	public function output() {
		/** @var QMX_Collector_Image_Sizes */
		$collector = $this->collector;
		/** @var QMX_Data_Image_Sizes */
		$data = $collector->get_data();

		echo '<div class="qm" id="' . esc_attr( $collector->id() ) . '">';

			if ( ! empty( $data->sizes ) ) {
				echo '<table class="qm-sortable">';
					echo '<caption class="qm-screen-reader-text">' . esc_html( $this->name() ) . '</caption>';
					echo '<thead>';
						echo '<tr>';

							echo '<th scope="col" class="qm-num qm-sorted-asc qm-sortable-column">';
								echo $this->build_sorter( __( '', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-sortable-column">';
								echo $this->build_sorter( __( 'ID', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-sortable-column">';
								echo $this->build_sorter( __( 'Uses', 'query-monitor-extend' ) );
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
						$uses    = 0;

						foreach ( $data->sizes as $id => $row ) {
							$ratio = array(
								$row['width'],
								$row['height']
							);

							if (
								! empty( $row['width'] )
								&& ! empty( $row['height'] )
							) {
								$ratio = array(
									$row['width'] / $row['_gcd'],
									$row['height'] / $row['_gcd']
								);
							}

							if ( $ratio === array( $row['width'], $row['height'] ) ) {
								$ratio = array( '&mdash;' );
							}

							$uses += $row['used'];

							echo '<tr data-qmx-image-size-width="' . esc_attr( $row['width'] ) . '" data-qmx-image-size-height="' . esc_attr( $row['height'] ) . '" data-qmx-image-size-ratio="' . esc_attr( $row['ratio'] ) . '">';
								echo '<td class="qm-num">' . esc_html( $row['num'] ) . '</td>';
								echo '<td class="qm-ltr">' . esc_html( $id ) . '</td>';
								echo '<td class="qm-num" data-qmx-image-size-count="' . esc_attr( $row['used'] ) . '">' . esc_html( $row['used'] ) . '</td>';
								echo '<td class="qm-num" data-qmx-image-size-width="' . esc_attr( $row['width'] ) . '">' . esc_html( $row['width'] ) . '</td>';
								echo '<td class="qm-num" data-qmx-image-size-height="' . esc_attr( $row['height'] ) . '">' . esc_html( $row['height'] ) . '</td>';
								echo '<td class="qm-num" data-qmx-image-size-ratio="' . esc_attr( $row['ratio'] ) . '" data-qm-sort-weight="' . esc_attr( $row['ratio'] ) . '">' . esc_html( implode( ':', $ratio ) ) . '</td>';
								echo '<td class="qm-num qm-true">' . ( $row['crop'] ? '<span class="dashicons dashicons-yes"></span>' : '' ) . '</td>';
								echo '<td class="qm-ltr">' . esc_html( $row['source'] ) . '</td>';
							echo '</tr>';

							if ( ! array_key_exists( $row['source'], $sources ) ) {
								$sources[ $row['source'] ] = 0;
							}

							$sources[ $row['source'] ]++;
						}

					echo '</tbody>';
					echo '<tfoot>';

						$sources = array_map(
							function ( $k, $v ) {
								return ucwords( ( string ) $k ) . ': ' . $v;
							},
							array_keys( $sources ),
							$sources
						);

						echo '<tr>';
							echo '<td colspan="2">Total: <span class="qm-items-number">' . esc_html( number_format_i18n( count( $data->sizes ) ) ) . '</span></td>';
							echo '<td>Uses: <span class="qm-items-number">' . esc_html( number_format_i18n( $uses ) ) . '</span></td>';
							echo '<td colspan="2">Duplicates: <span class="qm-items-number">' . esc_html( number_format_i18n( array_sum( $data->duplicates['dimensions'] ) ) ) . '</span></td>';
							echo '<td colspan="2">Duplicates: <span class="qm-items-number">' . esc_html( number_format_i18n( array_sum( $data->duplicates['ratios'] ) ) ) . '</span></td>';
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

		$this->current_id   = 'qm-image_sizes';
		$this->current_name = 'Image Sizes';

		$this->output_concerns();
	}

	/**
	 * @param array<string, array<string, mixed>> $menu
	 * @return array<string, array<string, mixed>>
	 */
	public function panel_menu( array $menu ) {
		$menu['qm-image_sizes'] = $this->menu( array(
			'title' => esc_html__( 'Image Sizes', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-image_sizes',
		) );

		return $menu;
	}
}

add_filter( 'qm/outputter/html', static function ( array $output ) : array {
	if ( $collector = QM_Collectors::get( 'image_sizes' ) ) {
		$output['image_sizes'] = new QMX_Output_Html_Image_Sizes( $collector );
	}

	return $output;
}, 70 );