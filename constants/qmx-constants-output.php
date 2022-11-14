<?php
/**
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/constants
 * Description: Query Monitor output for constants collector.
 * Version: 1.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

add_action( 'shutdown', static function () {

	if ( !class_exists( 'QMX_Collector_Constants' ) )
		return;

	$qm_dir = trailingslashit( QueryMonitor::init()->plugin_path() );

	if ( ! file_exists( $qm_dir . 'output/Html.php' ) )
		return;

	require_once $qm_dir . 'output/Html.php';

	class QMX_Output_Html_Constants extends QM_Output_Html {

		public function __construct( QM_Collector $collector ) {
			parent::__construct( $collector );
			add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
		}

		public function output() {
			$data = $this->collector->get_data();

			echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';

				if ( !empty( $data['constants'] ) ) {
					echo '<table class="qm-sortable">';
						echo '<caption class="qm-screen-reader-text">' . esc_html( $this->collector->name() ) . '</caption>';
						echo '<thead>';
							echo '<tr>';

								echo '<th scope="col" class="qm-num qm-sorted-asc qm-sortable-column">';
									echo $this->build_sorter( __( '', 'query-monitor-extend' ) );
								echo '</th>';

								echo '<th scope="col" class="qm-sortable-column">';
									echo $this->build_sorter( __( 'Constant', 'query-monitor-extend' ) );
								echo '</th>';

								echo '<th scope="col" class="qm-ltr">';
									echo __( 'Value', 'query-monitor-extend' );
								echo '</th>';

								echo '<th scope="col" class="qm-sortable-column">';
									echo $this->build_sorter( __( 'Type', 'query-monitor-extend' ) );
								echo '</th>';

							echo '</tr>';
						echo '</thead>';

						echo '<tbody>';

							$i = 1;
							$bools = array( true => 'true', false => 'false' );

							foreach ( $data['constants'] as $constant => $value ) {
								echo '<tr>';
									echo '<td class="qm-num">' . $i++ . '</td>';
									echo '<td class="qm-ltr" data-qm-sort-weight="' . strtolower( esc_attr( $constant ) ) . '"><code style="user-select: all;">' . esc_html( $constant ) . '</code></td>';
									echo '<td ' . ( is_bool( $value ) ? ' class="qm-' . $bools[$value] . '"' : '' ) . '>' . esc_html( QM_Util::display_variable( $value ) ) . '</td>';
									echo '<td class="qm-ltr">' . esc_html( gettype( $value ) ) . '</td>';
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
			$menu['constants'] = $this->menu( array(
				'title' => esc_html__( 'Constants', 'query-monitor-extend' ),
				'id'    => 'query-monitor-extend-constants',
			) );

			return $menu;
		}

	}

	add_filter( 'qm/outputter/html', static function ( array $output ) : array {
		if ( $collector = QM_Collectors::get( 'constants' ) )
			$output['constants'] = new QMX_Output_Html_Constants( $collector );

		return $output;
	}, 70 );

}, 9 );