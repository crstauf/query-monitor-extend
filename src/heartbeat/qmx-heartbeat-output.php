<?php
/**
 * Plugin Name: QMX: Heartbeat Output
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/heartbeat
 * Description: Query Monitor output for heartbeat collector.
 * Version: 1.0.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

add_action( 'shutdown', static function () {

	if ( !class_exists( 'QMX_Collector_Heartbeat' ) )
		return;

	if ( ! class_exists( 'QM_Dispatcher_Html' ) || ! QM_Dispatcher_Html::user_can_view() || ! QM_Dispatcher_Html::request_supported() ) {
		return;
	}

	if ( defined( 'QM_DISABLED' ) && constant( 'QM_DISABLED' ) ) {
		return;
	}

	if ( constant( 'QMX_DISABLED' ) ) {
		return;
	}

	if ( is_admin() ) {
		if ( ! ( did_action( 'admin_init' ) || did_action( 'admin_footer' ) ) ) {
			return;
		}
	} else {
		if ( ! ( did_action( 'wp' ) || did_action( 'wp_footer' ) || did_action( 'login_init' ) || did_action( 'gp_head' ) || did_action( 'login_footer' ) || did_action( 'gp_footer' ) ) ) {
			return;
		}
	}

	/** Back-compat filter. Please use `qm/dispatch/html` instead */
	if ( ! apply_filters( 'qm/process', true, is_admin_bar_showing() ) ) {
		return;
	}

	$qm = QueryMonitor::init()->plugin_path( 'assets/query-monitor.css' );

	if ( ! file_exists( $qm ) ) {
		return;
	}

	$qm_dir = trailingslashit( dirname( dirname( $qm ) ) );

	if ( ! file_exists( $qm_dir . 'output/Html.php' ) )
		return;

	require_once $qm_dir . 'output/Html.php';

	class QMX_Output_Html_Heartbeat extends QM_Output_Html {

		public function __construct( QM_Collector $collector ) {
			parent::__construct( $collector );
			add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
		}

		public function name() : string {
			return __( 'Heartbeat', 'query-monitor-extend' );
		}

		public function output() {
			$data = $this->collector->get_data();

			echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';

				if ( $this->collector->qm_no_jquery() ) {

					echo '<div class="qm-none">';
					echo '<p>Heartbeat logging requires jQuery, which has been prevented by <code>QM_NO_JQUERY</code>.</p>';
					echo '</div>';

				} else if ( wp_script_is( 'heartbeat', 'done' ) ) {
					echo '<table class="qm-sortable">';
						echo '<caption class="qm-screen-reader-text">' . esc_html( $this->collector->name() ) . '</caption>';

						echo '<thead>';
							echo '<tr>';

								echo '<th scope="col" class="qm-num"></th>';
								echo '<th scope="col">Lub</th>';
								echo '<th scope="col">Dub</th>';
								echo '<th scope="col">Time since previous</th>';
								echo '<th scope="col">Duration</th>';

							echo '</tr>';
						echo '</thead>';

						echo '<tbody>';
							echo '<tr class="listening">';
								echo '<td colspan="5">';
									echo '<div class="qm-none"><p>Listening for first heartbeat...</p></div>';
								echo '</td>';
							echo '</tr>';
						echo '</tbody>';

					echo '</table>';
					echo '<script type="text/javascript">qmx_heartbeat.populate_table();</script>';
				} else {

					echo '<div class="qm-none">';
					echo '<p>' . esc_html__( 'No heartbeat detected.', 'query-monitor' ) . '</p>';
					echo '</div>';

				}

			echo '</div>';

			$this->current_id = 'qm-heartbeat';
			$this->current_name = 'Heartbeat';

			$this->output_concerns();
		}

		public function panel_menu( array $menu ) {
			$menu['qm-heartbeat'] = $this->menu( array(
				'title' => esc_html__( 'Heartbeats (0)' ),
				'id'    => 'query-monitor-extend-heartbeat',
			) );

			return $menu;
		}

	}

	add_filter( 'qm/outputter/html', static function ( array $output ) : array {
		if ( $collector = QM_Collectors::get( 'heartbeat' ) )
			$output['heartbeat'] = new QMX_Output_Html_Heartbeat( $collector );

		return $output;
	}, 70 );

}, 9 );