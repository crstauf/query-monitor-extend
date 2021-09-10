<?php
/**
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/paths
 * Description: Query Monitor output for paths collector.
 * Version: 1.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

add_action( 'shutdown', static function () {

	if ( !class_exists( 'QMX_Collector_Paths' ) )
		return;

	$qm_dir = trailingslashit( QueryMonitor::init()->plugin_path() );
	require_once $qm_dir . 'output/Html.php';

	class QMX_Output_Html_Paths extends QM_Output_Html {

		public function __construct( QM_Collector $collector ) {
			parent::__construct( $collector );
			add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
		}

		public function output() {
			$data = $this->collector->get_data();

			echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';

				if ( !empty( $data['paths'] ) ) {
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

							foreach ( $data['paths'] as $var => $value ) {
								echo '<tr>';
									echo '<td class="qm-ltr"><code style="user-select: all;">' . esc_html( $var ) . '</code></td>';

									if ( is_string( $value ) ) {

										echo '<td>' . esc_html( $value ) . '</td>';

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
		}

		public function panel_menu( array $menu ) {

			$menu['paths'] = $this->menu( array(
				'title' => esc_html__( 'Paths', 'query-monitor-extend' ),
				'id'    => 'query-monitor-extend-paths',
			) );

			return $menu;

		}

	}

	add_filter( 'qm/outputter/html', static function ( array $output ) : array {
		if ( $collector = QM_Collectors::get( 'paths' ) )
			$output['paths'] = new QMX_Output_Html_Paths( $collector );

		return $output;
	}, 70 );

}, -1 );