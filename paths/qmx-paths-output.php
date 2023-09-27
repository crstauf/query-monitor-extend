<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @property-read QMX_Collector_Paths $collector
 */
class QMX_Output_Html_Paths extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function name() {
		return __( 'Paths', 'query-monitor-extend' );
	}

	public function output() {
		/** @var QMX_Data_Paths $data */
		$data = $this->collector->get_data();

		echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';

			if ( ! empty( $data->paths ) ) {
				echo '<table class="qm-sortable">';
					echo '<caption class="qm-screen-reader-text">' . esc_html( $this->collector->name() ) . '</caption>';
					echo '<thead>';
						echo '<tr>';

							echo '<th scope="col" class="qm-sorted-asc qm-sortable-column">';
								echo $this->build_sorter( __( 'Constant/Function', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-ltr">';
								echo __( 'Path', 'query-monitor-extend' );
							echo '</th>';

						echo '</tr>';
					echo '</thead>';

					echo '<tbody>';

						foreach ( $data->paths as $var => $value ) {
							echo '<tr>';
								echo '<td class="qm-ltr"><code style="user-select: all;">' . esc_html( $var ) . '</code></td>';

								if ( is_string( $value ) ) {

									# Remove ABSPATH and add back to support paths without ABSPATH.
									$possible_path = str_replace( ABSPATH, '', $value );
									$possible_path = ABSPATH . $possible_path;

									$value = esc_html( $value );

									if ( file_exists( $possible_path ) ) {
										$value = QM_Output_Html::output_filename( $value, $possible_path );
									}

									echo '<td>' . $value . '</td>';

								} else {

									echo '<td class="qm-has-inner qm-ltr">';
										self::output_inner( $value );
									echo '</td>';

								}
							echo '</tr>';
						}

					echo '</tbody>';
					echo '<tfoot>';

					echo '</tfoot>';
				echo '</table>';

			} else {

				echo '<div class="qm-none">';
				echo '<p>' . esc_html__( 'None', 'query-monitor' ) . '</p>';
				echo '</div>';

			}

		echo '</div>';

		$this->current_id   = 'qm-paths';
		$this->current_name = 'Paths';

		$this->output_concerns();
	}

	/**
	 * @param array<string, array<string, mixed>> $menu
	 * @return array<string, array<string, mixed>>
	 */
	public function panel_menu( array $menu ) {
		$menu['qm-paths'] = $this->menu( array(
			'title' => esc_html__( 'Paths', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-paths',
		) );

		return $menu;
	}

}

add_filter( 'qm/outputter/html', static function ( array $output ) : array {
	if ( $collector = QM_Collectors::get( 'paths' ) ) {
		$output['paths'] = new QMX_Output_Html_Paths( $collector );
	}

	return $output;
}, 70 );