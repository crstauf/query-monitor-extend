<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

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

					sort($data['constants']);

					foreach ( $data['constants'] as $constant ) {

						echo '<tr>';
							echo '<th><a href="https://www.google.com/?gws_rd=ssl#q=site%3Acodex.wordpress.org+OR+developer.wordpress.org+' . esc_url( $constant ) . '" target="_blank">' . esc_attr( $constant ) . '</a></th>';
							echo '<td title="' . ( defined( $constant ) ? gettype( constant( $constant ) ) : 'undefined' ) . '">' . css_qm_extend::get_format_value( $constant, true ) . '</td>';
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

class CSS_QM_Output_Html_Multisite extends QM_Output_Html {

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
						echo '<td title="' . ( defined( $constant ) ? gettype ( constant( $constant ) ) : 'undefined' ) . '">' . css_qm_extend::get_format_value( $constant, true ) . '</td>';
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
	}

	public function output() {

		$data = $this->collector->get_data();

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-two-thirds">';

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
									echo '<td>' . css_qm_extend::get_format_value( $v[$ks[0]] ) . '</td>';
								echo '</tr>';
								unset( $v[$ks[0]] );

								foreach ( $v as $kk => $vv ) {
									if ( is_array( $vv ) || is_object( $vv ) )
										$vv = gettype( $vv );
									echo '<tr>';
										echo '<th>' . esc_attr( $kk ) . '</th>';
										echo '<td colspan="2">' . css_qm_extend::get_format_value( $vv ) . '</td>';
									echo '</tr>';
								}

							} else {

								echo '<tr>';
									echo '<th>' . ( !in_array( $k, array( '', '<br />Template (parent)' ) ) && false === strpos( $k, '[' ) && ')' !== $k ? '<a href="https://www.google.com/?gws_rd=ssl#q=site%3Acodex.wordpress.org+OR+developer.wordpress.org+' . esc_attr( $k ) . '" target="_blank">' . esc_attr( $k ) . '</a>' : esc_attr( $k ) ) . '</th>';
									echo '<td colspan="2">' . css_qm_extend::get_format_value( $v ) . '</td>';
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

class CSS_QM_Output_Html_ImageSizes extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
	}

	public function output() {

		$data = $this->collector->get_data();

		ksort($data['imagesizes']);

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-third">';

			echo '<table cellspacing="0" class="qm-sortable">' .
				'<thead>' .
					'<tr>' .
						'<th colspan="4">Registered Image Sizes</th>' .
					'</tr>' .
					'<tr>' .
						'<th class="qm-sorted-asc">Name' .
							str_replace(
								'class="qm-sort-controls"',
								'class="qm-sort-controls" style="text-align: left !important;"',
								$this->build_sorter()
							) . '</th>' .
						'<th class="qm-num qm-imagesize-width" style="width: 50px;">Width' . $this->build_sorter() . '</th>' .
						'<th class="qm-num qm-imagesize-height" style="width: 50px;">Height' . $this->build_sorter() . '</th>' .
						'<th style="width: 65px;">' .
							'Built-in ' .
							'<select id="qm-filter-imagesizes-builtin" class="qm-filter" data-filter="imagesize" data-highlight="">' .
								'<option value="">All</option>' .
								'<option value="builtin">Built-in</option>' .
								'<option value="additional">Additional</option>' .
							'</select>' .
						'</th>' .
					'</tr>' .
				'</thead>' .
				'<tfoot>' .
					'<tr>' .
						'<td colspan="4" style="text-align: right !important;">Count: ' . count($data['imagesizes']) . '</td>' .
					'</tr>' .
				'</tfoot>' .
				'<tbody>';

					foreach ($data['imagesizes'] as $name => $details) {
						$is_builtin = array_key_exists('_builtin',$details) && true === $details['_builtin'];
						$is_crop = true === $details['crop'];

						echo '<tr id="qm-imagesize-' . esc_attr($name) . '" class="' . ($is_builtin ? 'qm-imagesizes-builtin' : '') . ($is_crop ? ' qm-imagesize-crop' : '') . '" data-qm-imagesize="' . ($is_builtin ? 'builtin' : 'additional') . '">' .
							'<td class="qm-ltr">' .
								esc_html($name) .
							'</td>' .
							'<td class="qm-num qm-imagesize-width' . (!$is_crop ? ' qm-info' : '') . '">' .
								esc_html($details['width']) .
							'</td>' .
							'<td class="qm-num qm-imagesize-height' . (!$is_crop ? ' qm-info' : '') . '">' .
								esc_html($details['height']) .
							'</td>' .
							'<td class="qm-ltr' . ($is_builtin ? ' qm-true' : '') . '" style="text-align: center !important;">' .
								($is_builtin ? '&#10003;' : '') .
							'</td>' .
						'</tr>';
					}

				echo '</tbody>' .
            '</table>' .
			'<style type="text/css">.qm-hide-imagesize { display: none !important; }</style>' .
        '</div>';

	}

}

?>
