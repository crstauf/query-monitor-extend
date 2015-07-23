<?php

class CSS_QM_Output_Html_Constants extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 110 );
	}

	public function output() {

		$data = $this->collector->get_data();

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-half">';

		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="2">Constants</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $data['constants'] as $key => $val ) {

			echo '<tr>';
			echo "<td><a href='https://www.google.com/?gws_rd=ssl#q=site:codex.wordpress.org+{$key}' target='_blank'>{$key}</a></td>";
			echo "<td>{$val}</td>";
			echo '</tr>';

		}

		echo '</tbody>';
		echo '<tfoot>';
		echo '<tr>';
		echo '<th colspan="2">Reference: <a href="http://wpengineer.com/2382/wordpress-constants-overview/" target="_blank">http://wpengineer.com/2382/wordpress-constants-overview/</a><br />Please note that all QM data is called via AJAX, so some constants are not accurate.</th>';
		echo '</tr>';
		echo '</tfoot>';
		echo '</table>';

		echo '</div>';

	}

}

class CSS_QM_Output_Html_Multisite extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 110 );
	}

	public function output() {

		$data = $this->collector->get_data();

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-half">';

		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="2">Multisite Constants</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $data['multisite'] as $key => $val ) {

			echo '<tr>';
			echo '<td>' . (!in_array($key,array('','<br />Template (parent)')) && false === strpos($key,'[') && ')' !== $key ? '<a href="https://www.google.com/?gws_rd=ssl#q=site:codex.wordpress.org+' . $key . '" target="_blank">' . $key . '</a>' : $key ) . '</td>';
			echo "<td>{$val}</td>";
			echo '</tr>';

		}

		echo '</tbody>';
		echo '</table>';

		echo '</div>';

	}

}

class CSS_QM_Output_Html_Paths extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 110 );
	}

	public function output() {

		$data = $this->collector->get_data();

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-full qm-clear">';

		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="2">Paths</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $data['paths'] as $key => $val ) {

			echo '<tr>';
			echo '<td>' . (!in_array($key,array('','<br />Template (parent)')) && false === strpos($key,'[') && ')' !== $key ? '<a href="https://www.google.com/?gws_rd=ssl#q=site:codex.wordpress.org+' . $key . '" target="_blank">' . $key . '</a>' : $key ) . '</td>';
			echo "<td>{$val}</td>";
			echo '</tr>';

		}

		echo '</tbody>';
		echo '</table>';

		echo '</div>';

	}

}

class CSS_QM_Output_Html_VarDumps extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		if (!count(css_qm_extend::$var_dumps)) return;
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 110 );
	}

	public function output() {

		$data = $this->collector->get_data();

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
