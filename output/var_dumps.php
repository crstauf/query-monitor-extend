<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Output_Html_VarDumps extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
	}

	public function output() {

		$data = $this->collector->get_data();
		if (!count(cssllc_query_monitor_extend::$var_dumps)) return;

		echo '<span id="' . esc_attr( $this->collector->id() ) . '"></span>';

		foreach ($data['vardumps'] as $id => $var)
			self::div($id,$var[0]);

	}

	public function div($id,$var,$sub = '') {
		$temp = explode('_',$id);
		$label = $temp[1];
		unset($temp);
		echo '<div id="' . esc_attr( $this->collector->id() ) . '_' . $id . str_replace('->','_',str_replace('[','_',str_replace(']','',$sub))) . '" class="qm">';

		echo '<table cellspacing="0">';
		echo '<header><tr><td>' . $label . $sub . '</td></tr></thead>';
        echo '<tbody><tr><td>';

		self::output_inner($var);

		echo '</td></tr></tbody>';
		echo '</table>';

		echo '</div>';

	}

}

function register_qmx_output_html_vardumps( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'qmx-var_dumps' ) )
		$output['qmx-var_dumps'] = new QMX_Output_Html_VarDumps( $collector );
	return $output;
}

?>
