<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @property-read QMX_Collector_Heartbeat $collector
 */
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

		$this->current_id   = 'qm-heartbeat';
		$this->current_name = 'Heartbeat';

		$this->output_concerns();
	}

	/**
	 * @param array<string, array<string, mixed>> $menu
	 * @return array<string, array<string, mixed>>
	 */
	public function panel_menu( array $menu ) {
		$menu['qm-heartbeat'] = $this->menu( array(
			'title' => esc_html__( 'Heartbeats (0)' ),
			'id'    => 'query-monitor-extend-heartbeat',
		) );

		return $menu;
	}
}

add_filter( 'qm/outputter/html', static function ( array $output ) : array {
	if ( $collector = QM_Collectors::get( 'heartbeat' ) ) {
		$output['heartbeat'] = new QMX_Output_Html_Heartbeat( $collector );
	}

	return $output;
}, 70 );