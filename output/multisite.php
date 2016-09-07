<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class QMX_Output_Html_Multisite extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 110 );
	}

	public function output() {

		$data = $this->collector->get_data();

		sort( $data['multisite'] );

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-third"">';

			echo '<table cellspacing="0">';
				echo '<thead>';
					echo '<tr>';
						echo '<th colspan="2">Multisite Constants</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';

				foreach ( $data['multisite'] as $constant ) {

					echo '<tr>';
						echo '<th><a href="https://www.google.com/?gws_rd=ssl#q=site%3Acodex.wordpress.org+OR+developer.wordpress.org+' . esc_url( $constant ) . '" target="_blank">' . esc_attr( $constant ) . '</a></th>';
						echo '<td title="' . ( defined( $constant ) ? gettype ( constant( $constant ) ) : 'undefined' ) . '">' . query_monitor_extend::get_format_value( $constant, true ) . '</td>';
					echo '</tr>';

				}

				echo '</tbody>';
				echo '<tfoot>';
					echo '<tr>';
						echo '<th colspan="2">Reference: <a href="http://wpengineer.com/2382/wordpress-constants-overview/" target="_blank">wpengineer.com/2382/wordpress-constants-overview/</a><br />Please note that constants/paths may not accurately reflect the page you are currently viewing.</th>';
					echo '</tr>';
				echo '</tfoot>';
			echo '</table>';

		echo '</div>';

	}

}

function register_qmx_output_html_multisite( array $output, QM_Collectors $collectors ) {
	if ( is_multisite() && $collector = QM_Collectors::get( 'qmx-multisite' ) )
		$output['qmx-multisite'] = new QMX_Output_Html_Multisite( $collector );
	return $output;
}

?>
