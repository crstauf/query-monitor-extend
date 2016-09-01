<?php
/*
Copyright 2009-2016 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class QMX_Output_Html_Transients extends QM_Output_Html_Transients {

	private $count = 0;
	private $oldest_transient = array();
	private $expired_transients = 0;

	public function get_expired_transients_count() {
		global $wpdb;

		$time = time();
		$oldest = array( 1 => $time );
		$this->expired_transients = 0;

		/* queries copied from wp-admin/includes/schema.php */
		$sql = "SELECT a.option_name,b.option_value FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %d";
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', $time ), ARRAY_N );

		if ( is_main_site() && is_main_network() ) {
			$sql = "SELECT a.option_name,b.option_value FROM $wpdb->options a, $wpdb->options b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT( '_site_transient_timeout_', SUBSTRING( a.option_name, 17 ) )
				AND b.option_value < %d";
			$results += $wpdb->get_results( $wpdb->prepare( $sql, $wpdb->esc_like( '_site_transient_' ) . '%', $wpdb->esc_like( '_site_transient_timeout_' ) . '%', $time ), ARRAY_N );
		}

		$this->expired_transients = count( $results );

		if ( is_array( $results ) && count( $results ) )
			foreach ( $results as $row )
				if ( intval( $row[1] ) < $oldest[1] )
					$oldest = $row;

		$this->oldest_transient = $oldest;
	}

	public function output() {
		$this->get_expired_transients_count();

		$data = array( 'trans' => array() );
		$temp = $this->collector->get_data();

		$colspan = 4;

		if ( isset( $temp['trans'] ) && !empty( $temp['trans'] ) ) {
			foreach ( $temp['trans'] as $i => $array )
				$data['trans'][$array['transient']] = $array;

			$data = array_merge($data,$this->collector->additional_data);
			$this->count = count( $data['trans'] );

			$temp = $data['trans'];
			$first = array_shift( $temp );
		}

		echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';
		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>' . esc_html__( 'Transient Set', 'query-monitor' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'query-monitor' ) . '</th>';
		if ( is_multisite() ) {
			$colspan++;
			echo '<th>' . esc_html__( 'Type', 'query-monitor' ) . '</th>';
		}
		if ( !empty( $data['trans'] ) and isset( $first['expiration'] ) ) {
			$colspan++;
			echo '<th class="qm-num">' . esc_html__( 'Expiration', 'query-monitor' ) . '</th>';
		}
		echo '<th>' . esc_html__( 'Call Stack', 'query-monitor' ) . '</th>';
		echo '<th>' . esc_html__( 'Component', 'query-monitor' ) . '</th>';
		echo '</tr>';
		echo '</thead>';

		echo '<tfoot>';
		echo '<tr><td colspan="' . $colspan . '">Total Transients Set: ' . $this->count . '</td></tr>';
		echo '<tr><td colspan="' . $colspan . '">';
		echo 'Total Expired Transients: ' . $this->expired_transients;
		if ( 0 !== $this->expired_transients )
			echo '&nbsp;&nbsp;&nbsp;<span title="' . esc_attr( str_replace( array( '_site_transient_', '_transient_' ), '', $this->oldest_transient[0] ) ) . '">Oldest Expired Transient: ' . $this->time_elapsed_string( $this->oldest_transient[1] ) . ' old</span>';
		echo '</td></tr>';
		echo '</tfoot>';

		$stati = array(
			'updated' => 'Option value changed.',
			'extended' => 'Expiration time extended; value did not change.',
			'added' => 'New option added.',
		);

		if ( !empty( $data['trans'] ) ) {

			echo '<tbody>';

			foreach ( $data['trans'] as $row ) {
				$transient = str_replace( array(
					'_site_transient_',
					'_transient_'
				), '', $row['transient'] );

				$component = $row['trace']->get_component();

				echo '<tr>';
				printf(
					'<td>%s</td>',
					esc_html( $transient )
				);
				printf(
					'<td><abbr title="%s">%s</abbr></td>',
					esc_attr( $stati[$row['status']] ),
					esc_html( ucwords( $row['status'] ) )
				);
				if ( is_multisite() ) {
					printf(
						'<td>%s</td>',
						esc_html( $row['type'] )
					);
				}

				if ( isset( $row['expiration'] ) ) {
					if ( 0 === $row['expiration'] ) {
						printf(
							'<td><em>%s</em></td>',
							esc_html__( 'none', 'query-monitor' )
						);
					} else {
						printf(
							'<td class="qm-num">%s</td>',
							$this->convert_secs( $row['expiration'] )
						);
					}
				}

				$stack          = array();
				$filtered_trace = $row['trace']->get_filtered_trace();
				array_shift( $filtered_trace );

				foreach ( $filtered_trace as $item ) {
					$stack[] = self::output_filename( $item['display'], $item['calling_file'], $item['calling_line'] );
				}

				printf( // WPCS: XSS ok.
					'<td class="qm-nowrap qm-ltr">%s</td>',
					implode( '<br>', $stack )
				);
				printf(
					'<td class="qm-nowrap">%s</td>',
					esc_html( $component->name )
				);

				echo '</tr>';

			}

			echo '</tbody>';

		} else {

			echo '<tbody>';
			echo '<tr>';
			echo '<td colspan="4" style="text-align:center !important"><em>' . esc_html__( 'none', 'query-monitor' ) . '</em></td>';
			echo '</tr>';
			echo '</tbody>';

		}

		echo '</table>';
		echo '</div>';

	}

	/* adapted from https://gist.github.com/erickpatrick/3039081 */
	public function convert_secs($secs)
	{
		if ( $secs == 0 ) return '0<abbr title="seconds">s</abbr>';
        $units = array(
            "weeks"   => 7*24*3600,
            "days"    =>   24*3600,
            "hours"   =>      3600,
            "minutes" =>        60,
            "seconds" =>         1,
        );
        $s = "";
        foreach ( $units as $name => $divisor ) {
                if ( $quot = intval($secs / $divisor) ) {
                        $s .= $quot . '<abbr title="' . esc_attr( $name ) . '">' . substr( $name, 0, 1 ) . '</abbr>';
                        $secs -= $quot * $divisor;
                }
        }
		return $s;
	}

	/* adapted from https://gist.github.com/zachstronaut/1184831 */
	public function time_elapsed_string($ptime) {
	    $etime = time() - $ptime;

	    if ($etime < 1) {
	        return 'now';
	    }

	    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
	                30 * 24 * 60 * 60       =>  'month',
	                24 * 60 * 60            =>  'day',
	                60 * 60                 =>  'hour',
	                60                      =>  'minute',
	                1                       =>  'second'
	                );

	    foreach ($a as $secs => $str) {
	        $d = $etime / $secs;
	        if ($d >= 1) {
	            $r = round($d);
	            return $r . ' ' . $str . ($r > 1 ? 's' : '');
	        }
	    }
	}

	public function admin_menu( array $menu ) {

		$data  = $this->collector->get_data();

		$title = __( 'Transients Set (%s/%s)', 'query-monitor' );

		$menu[] = $this->menu( array(
			'title' => esc_html( sprintf(
				$title,
				number_format_i18n( $this->count ),
				number_format_i18n( $this->expired_transients )
			) ),
		) );
		return $menu;

	}

}

function unregister_qm_output_html_transients( array $output ) {
    remove_filter( 'qm/outputter/html', 'register_qm_output_html_transients', 100, 2 );
    return $output;
}

function register_qmx_output_html_transients( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'transients' ) ) {
		$output['transients'] = new QMX_Output_Html_Transients( $collector );
	}
	return $output;
}
