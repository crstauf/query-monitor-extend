<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class QMX_Output_Html_Time extends QM_Output_Html {

    public function __construct( QM_Collector $collector ) {
        parent::__construct( $collector );
    }

    public function output() {

        $utc_formatted = gmdate( 'D, M j, Y H:i:s' );

        $server_formatted = date( 'D, M j, Y H:i:s' );
        $server_timezone = 'GMT' . date( 'O' ) . ' (' . date( 'T' ) . ')';
        $server_offset = intval( date( 'Z' ) );

        $wp_timestamp = current_time( 'timestamp' );
        $wp_timezone =  'GMT' . date( 'O', $wp_timestamp ) . ' (' . date( 'T', $wp_timestamp ) . ')';
        $wp_formatted = date( 'D, M j, Y H:i:s', $wp_timestamp );
        $wp_offset = get_option( 'gmt_offset' );

        echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm">';

        echo '<table cellspacing="0">';
            echo '<thead>';
                echo '<tr>';
                    echo '<th style="width: 25%;">GMT/UTC Time</th>';
                    echo '<th style="width: 25%;">Server</th>';
                    echo '<th style="width: 25%;">WordPress</th>';
                    echo '<th style="width: 25%;">Browser</th>';
                echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
                echo '<tr>';
                    echo '<td class="utc">' . esc_html( $utc_formatted ) . '</td>';
                    echo '<td class="server" title="' . esc_attr( $server_timezone ) . '">' . esc_html( $server_formatted ) . '</td>';
                    echo '<td class="wp" data-offset="' . esc_attr( $wp_offset * HOUR_IN_SECONDS ) . '" title="' . esc_attr( $wp_timezone ) . '">' . esc_html( $wp_formatted ) . '</td>';
                    echo '<td class="browser">&mdash;</td>';
                echo '</tr>';
            echo '</tbody>';
        echo '</table>';

        echo '</div>';

    }

}

function register_qmx_output_html_time( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'qmx-time' ) )
		$output['qmx-time'] = new QMX_Output_Html_Time( $collector );
	return $output;
}

?>
