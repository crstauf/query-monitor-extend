<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class CSSLLC_QMX_Output_Html_VarDumps extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
	}

	public function output() {

		$data = $this->collector->get_data();
		if (!count(cssllc_query_monitor_extend::$var_dumps)) return;

		echo '<span id="' . esc_attr( $this->collector->id() ) . '"></span>';

		foreach ($data['vardumps'] as $id => $var)
			self::div($id,$var[0],'',$var[1]);

	}

	public function div($id,$var,$sub = '',$single_table = false) {
		$temp = explode('_',$id);
		$label = $temp[1];
		unset($temp);
		echo '<div id="' . esc_attr( $this->collector->id() ) . '_' . $id . str_replace('->','_',str_replace('[','_',str_replace(']','',$sub))) . '" class="qm qm-' . (true === $single_table ? 'full qm-clear' : 'half') . '">';

		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="2">' . $label . $sub . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		$divs = array();
		foreach ( $var as $key => $val ) {

			$display = $val;
			if (is_object($val) || is_array($val)) {
				$time = time();
				if (false === $single_table) {
					$divs[] = array($time,$label . $sub,$key,$val);
					$display = '<a href="#qm-vardumps_' . $time . '_' . $label . '_' . $key . '">' . ucfirst(gettype($val)) . '</a>';
				} else
					$display = '<pre>' . print_r($val,true) . '</pre>';
			}

			echo '<tr>';
			echo "<td>{$key}</td>";
			echo '<td>' . $display . '</td>';
			echo '</tr>';

		}

		echo '</tbody>';
		echo '</table>';

		echo '</div>';

		if (count($divs))
			foreach ($divs as $div)
				self::div($div[0] . '_' . $div[1],$div[3],(is_array($div[3]) ? '[' . $div[2] . ']' : '->' . $div[2]));

	}

}

function register_cssllc_qmx_output_html_vardumps( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'vardumps' ) )
		$output['vardumps'] = new CSSLLC_QMX_Output_Html_VarDumps( $collector );
	return $output;
}

add_filter( 'qm/outputter/html', 'register_cssllc_qmx_output_html_vardumps', 130, 2 );

?>
