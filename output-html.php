<?php

class CSS_QM_Output_Html_Constants extends QM_Output_Html {

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

		foreach ( $data['constants'] as $key => $val ) {

			echo '<tr>';
			echo "<td><a href='https://www.google.com/?gws_rd=ssl#q=site:codex.wordpress.org+{$key}' target='_blank'>{$key}</a></td>";
			echo "<td>{$val}</td>";
			echo '</tr>';

		}

		echo '</tbody>';
		echo '<tfoot>';
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
		echo '<tfoot>';
		echo '<tr>';
		echo '<th colspan="2">Reference: <a href="http://wpengineer.com/2382/wordpress-constants-overview/" target="_blank">wpengineer.com/2382/wordpress-constants-overview/</a><br />Please note that constants/paths may not accurately reflect the page you are currently viewing.</th>';
		echo '</tr>';
		echo '</tfoot>';
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

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-third" style="width: 66.5% !important;">';

		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="3">Paths</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $data['paths'] as $i => $group ) {

			ksort($group);

			if (0 !== $i)
				echo '<tr><td colspan="3">&nbsp;</td></tr>';

			foreach ( $group as $k => $v ) {

				if ( is_array( $v ) || is_object( $v ) ) {

					$ks = array_keys ( $v );

					echo '<tr>';
						echo '<th rowspan="' . count( $v ) . '">' . ( !in_array( $k, array( '', '<br />Template (parent)' ) ) && false === strpos( $k, '[' ) && ')' !== $k ? '<a href="https://www.google.com/?gws_rd=ssl#q=site%3Acodex.wordpress.org+OR+developer.wordpress.org+' . $k . '" target="_blank">' . esc_attr( $k ) . '</a>' : esc_attr( $k ) ) . '</th>';
						echo '<td>' . $ks[0] . '</td>';
						echo '<td>' . $v[$ks[0]] . '</td>';
					echo '</tr>';
					unset($v[$ks[0]]);

					foreach ( $v as $kk => $vv ) {
						if ( is_array( $vv ) || is_object( $vv ) )
							$vv = gettype( $vv );
						echo '<tr>';
							echo '<th>' . esc_attr( $kk ) . '</th>';
							echo '<td colspan="2">' . esc_attr( $vv ) . '</td>';
						echo '</tr>';
					}

				} else {

					echo '<tr>';
						echo '<th>' . ( !in_array( $k, array( '', '<br />Template (parent)' ) ) && false === strpos( $k, '[' ) && ')' !== $k ? '<a href="https://www.google.com/?gws_rd=ssl#q=site%3Acodex.wordpress.org+OR+developer.wordpress.org+' . esc_attr( $k ) . '" target="_blank">' . esc_attr( $k ) . '</a>' : esc_attr( $k ) ) . '</th>';
						echo '<td colspan="2">' . esc_attr( $v ) . '</td>';
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

class CSS_QM_Output_Html_VarDumps extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 110 );
	}

	public function output() {

		$data = $this->collector->get_data();
		if (!count(css_qm_extend::$var_dumps)) return;

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
