<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Output_Html_Paths extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
	}

	public function output() {

		$data = $this->collector->get_data();

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-half">';

			echo '<table cellspacing="0">';
				echo '<thead>';
					echo '<tr>';
						echo '<th colspan="3">Paths</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';

					foreach ( $data['paths'] as $i => $group ) {

						ksort( $group );

						if ( 0 !== $i )
							echo '<tr><td colspan="3">&nbsp;</td></tr>';

						foreach ( $group as $k => $v ) {

							if ( is_array( $v ) || is_object( $v ) ) {

								$ks = array_keys( $v );

								echo '<tr>';
									echo '<th rowspan="' . count( $v ) . '"><a href="https://www.google.com/?gws_rd=ssl#q=site%3Acodex.wordpress.org+OR+developer.wordpress.org+' . esc_url( $k ) . '" target="_blank">' . esc_attr( $k ) . '</a></th>';
									echo '<td>' . esc_attr( $ks[0] ) . '</td>';
									echo '<td>' . query_monitor_extend::get_format_value( $v[$ks[0]] ) . '</td>';
								echo '</tr>';
								unset( $v[$ks[0]] );

								foreach ( $v as $kk => $vv ) {
									if ( is_array( $vv ) || is_object( $vv ) )
										$vv = gettype( $vv );
									echo '<tr>';
										echo '<th>' . esc_attr( $kk ) . '</th>';
										echo '<td colspan="2">' . query_monitor_extend::get_format_value( $vv ) . '</td>';
									echo '</tr>';
								}

							} else {

								echo '<tr>';
									echo '<th>' . ( !in_array( $k, array( '', '<br />Template (parent)' ) ) && false === strpos( $k, '[' ) && ')' !== $k ? '<a href="https://www.google.com/?gws_rd=ssl#q=site%3Acodex.wordpress.org+OR+developer.wordpress.org+' . esc_attr( $k ) . '" target="_blank">' . esc_attr( $k ) . '</a>' : esc_attr( $k ) ) . '</th>';
									echo '<td colspan="2">' . query_monitor_extend::get_format_value( $v ) . '</td>';
								echo '</tr>';

							}

						}

					}

				echo '</tbody>';
				echo '<tfoot>';
					echo '<tr>';
						echo '<th colspan="3">Reference: <a href="http://wpengineer.com/2382/wordpress-constants-overview/" target="_blank">wpengineer.com/2382/wordpress-constants-overview/</a><br />Please note that some paths may not accurately reflect the page you are currently viewing.</th>';
					echo '</tr>';
				echo '</tfoot>';
			echo '</table>';

		echo '</div>';

	}

}

function register_qmx_output_html_paths( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'qmx-paths' ) )
		$output['qmx-paths'] = new QMX_Output_Html_Paths( $collector );
	return $output;
}

?>
