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
