<?php
/**
 * Var dumps output for HTML pages.
 *
 * @package query-monitor-extend
 */

class QMX_Output_Html_Var_Dumps extends QMX_Output_Html {

	public function __construct( QMX_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function output() {
		$data = $this->collector->get_data();

		echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';

			if ( !empty( $data['vars'] ) ) {
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

							echo '<th scope="col">';
								echo 'Value';
							echo '</th>';

						echo '</tr>';
					echo '</thead>';

					echo '<tbody>';

						$i = 1;

						foreach ( $data['vars'] as $label => $value ) {
							echo '<tr>';
								echo '<td class="qm-num">' . $i++ . '</td>';
								echo '<td class="qm-ltr">' . esc_html( $label ) . '</td>';
								echo '<td>';
									echo '<textarea style="font-family: Consolas, Monaco, monospace; width: 100%; height: 200px; background-color: rgba( 255, 255, 255, 0.25 ); font-size: inherit;" readonly="readonly">';
										print_r( $value );
									echo '</textarea>';
								echo '</td>';
							echo '</tr>';
						}

					echo '</tbody>';

				echo '</table>';
			} else {

				echo '<div class="qm-none">';
				echo '<p>' . esc_html__( 'None', 'query-monitor' ) . '</p>';
				echo '</div>';

			}

		echo '</div>';
	}

	public function panel_menu( array $menu ) {
		$data = $this->collector->get_data();

		$menu['var_dumps'] = $this->menu( array(
			'title' => esc_html__( 'Var Dumps' . ( !empty( $data['vars'] ) ? ' (' . count( $data['vars'] ) . ')' : '' ), 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-var-dumps',
		) );

		return $menu;
	}

}

function register_qmx_output_html_var_dumps( array $output ) {
	if ( $collector = QMX_Collectors::get( 'var_dumps' ) ) {
		$output['var_dumps'] = new QMX_Output_Html_Var_Dumps( $collector );
	}
	return $output;
}

add_filter( 'qmx/outputter/html', 'register_qmx_output_html_var_dumps', 70 );