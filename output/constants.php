<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class CSSLLC_QMX_Output_Html_Constants extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 111 );
	}

	public function output() {

		$data = $this->collector->get_data();

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-third">';

			echo '<table cellspacing="0">';
				echo '<thead>';
					echo '<tr>';
						echo '<th colspan="2">Constants</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';

					sort($data['constants']);

					foreach ( $data['constants'] as $constant ) {

						echo '<tr>';
							echo '<th><a href="https://www.google.com/?gws_rd=ssl#q=site%3Acodex.wordpress.org+OR+developer.wordpress.org+' . esc_url( $constant ) . '" target="_blank">' . esc_attr( $constant ) . '</a></th>';
							echo '<td title="' . ( defined( $constant ) ? gettype( constant( $constant ) ) : 'undefined' ) . '">' . cssllc_query_monitor_extend::get_format_value( $constant, true ) . '</td>';
						echo '</tr>';

					}

				echo '</tbody>';
				echo '<tfoot>';
					echo '<tr>' .
						'<td colspan="2" style="text-align: right !important;">Count: ' . count($data['constants']) . '</td>' .
					'</tr>';
					echo '<tr>';
						echo '<th colspan="2">Reference: <a href="http://wpengineer.com/2382/wordpress-constants-overview/" target="_blank">wpengineer.com/2382/wordpress-constants-overview/</a><br />Please note that some constants may not accurately reflect the page you are currently viewing.</th>';
					echo '</tr>';
				echo '</tfoot>';
			echo '</table>';

		echo '</div>';

	}

	public function admin_menu( array $menu ) {
		$constants = array();
		if (defined('W3TC') && W3TC)
			$constants = array_merge($constants,array(
				'DONOTCACHEPAGE',
				'DONOTCACHEDB',
				'DONOTMINIFY',
				'DONOTCDN',
				'DONOTCACHCEOBJECT'
			));
		foreach (apply_filters('qmx/collect/conditionals/constants',$constants) as $constant) {
			if (defined($constant) && true === !!constant($constant))
				$menu[] = $this->menu( array(
					'title' => esc_html( $constant ),
					'id'    => 'query-monitor-constant-' . esc_attr( $constant ),
					'meta'  => array( 'classname' => 'qm-true qm-ltr' )
				) );
		}

		return $menu;
	}

}

function register_cssllc_qmx_output_html_constants( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'constants' ) )
		$output['constants'] = new CSSLLC_QMX_Output_Html_Constants( $collector );
	return $output;
}

?>
