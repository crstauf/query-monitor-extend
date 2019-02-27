<?php
/**
 * Heartbeat output for HTML pages.
 *
 * @package query-monitor-extend
 */

class QMX_Output_Html_Heartbeat extends QMX_Output_Html {

	public function __construct( QMX_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
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

							echo '<th scope="col" class="qm-num qm-sorted-asc qm-sortable-column"></th>';
							echo '<th scope="col">Lub</th>';
							echo '<th scope="col">Dub</th>';
							echo '<th scope="col">Time since last</th>';
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
	}

	public function panel_menu( array $menu ) {
		$data = $this->collector->get_data();

		$menu['heartbeat'] = $this->menu( array(
			'title' => esc_html__( 'Heartbeats (0)' ),
			'id'    => 'query-monitor-extend-heartbeat',
		) );

		return $menu;
	}

}

function register_qmx_output_html_heartbeat( array $output ) {
	if ( $collector = QMX_Collectors::get( 'heartbeat' ) ) {
		$output['heartbeat'] = new QMX_Output_Html_Heartbeat( $collector );
	}
	return $output;
}

add_filter( 'qmx/outputter/html', 'register_qmx_output_html_heartbeat', 70 );