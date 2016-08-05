<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/* based on QM v2.12.0 */
class QMX_Output_Html_PHP_Errors extends QM_Output_Html_PHP_Errors {

    public function output() {

		$data = $this->collector->get_data();

		if ( empty( $data['errors'] ) ) {
			return;
		}

		echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';
		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="2">' . esc_html__( 'PHP Error', 'query-monitor' ) . '</th>';
		echo '<th class="qm-num">' . esc_html__( 'Count', 'query-monitor' ) . '</th>';
		echo '<th>' . esc_html__( 'Location', 'query-monitor' ) . '</th>';
		echo '<th>' . esc_html__( 'Call Stack', 'query-monitor' ) . '</th>';
		echo '<th>' . esc_html__( 'Component', 'query-monitor' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		$types = array(
			'warning'               => _x( 'Warning', 'PHP error level', 'query-monitor' ),
			'notice'                => _x( 'Notice', 'PHP error level', 'query-monitor' ),
			'strict'                => _x( 'Strict', 'PHP error level', 'query-monitor' ),
			'deprecated'            => _x( 'Deprecated', 'PHP error level', 'query-monitor' ),
			'warning-suppressed'    => _x( 'Warning (Suppressed)', 'Suppressed PHP error level', 'query-monitor' ),
			'notice-suppressed'     => _x( 'Notice (Suppressed)', 'Suppressed PHP error level', 'query-monitor' ),
			'strict-suppressed'     => _x( 'Strict (Suppressed)', 'Suppressed PHP error level', 'query-monitor' ),
			'deprecated-suppressed' => _x( 'Deprecated (Suppressed)', 'Suppressed PHP error level', 'query-monitor' ),
		);

		foreach ( $types as $type => $title ) {

			if ( isset( $data['errors'][$type] ) ) {

				echo '<tr>';
				echo '<td rowspan="' . count( $data['errors'][$type] ) . '">' . esc_html( $title ) . '</td>';
				$first = true;

				foreach ( $data['errors'][$type] as $error ) {

					if ( !$first ) {
						echo '<tr>';
					}

					$component = $error->trace->get_component();
					$message   = wp_strip_all_tags( $error->message );

					echo '<td>' . esc_html( $message ) . '</td>';
					echo '<td>' . esc_html( number_format_i18n( $error->calls ) ) . '</td>';
					echo '<td>';
					echo self::output_filename( $error->filename . ':' . $error->line, $error->file, $error->line ); // WPCS: XSS ok.
					echo '</td>';

					$stack          = array();
					$filtered_trace = $error->trace->get_filtered_trace();

					// debug_backtrace() (used within QM_Backtrace) doesn't like being used within an error handler so
					// we need to handle its somewhat unreliable stack trace items.
					// https://bugs.php.net/bug.php?id=39070
					// https://bugs.php.net/bug.php?id=64987
					foreach ( $filtered_trace as $i => $item ) {
						if ( isset( $item['file'] ) && isset( $item['line'] ) ) {
							$stack[] = self::output_filename( $item['display'], $item['file'], $item['line'] );
						} else if ( 0 === $i ) {
							$stack[] = self::output_filename( $item['display'], $error->file, $error->line );
						} else {
							$stack[] = $item['display'] . '<br>&nbsp;<span class="qm-info"><em>' . __( 'Unknown location', 'query-monitor' ) . '</em></span>';
						}
					}

					echo '<td class="qm-row-caller qm-row-stack qm-nowrap qm-ltr' . ( 1 < count( $stack ) ? ' qm-has-toggle' : '' ) . '">';
                    if ( 1 < count( $stack ) ) {
                        echo '<div class="qm-toggler">';
                        echo $stack[0];
                        unset( $stack[0] );
                        echo '<a href="#" class="qm-toggle" data-on="+" data-off="-">+</a>';
                        echo '<div class="qm-toggled">';
                            echo implode( '<br>', $stack ); // WPCS: XSS ok.
                        echo '</div>';
                        echo '</div>';
                    } else
					    echo implode( '<br>', $stack ); // WPCS: XSS ok.
					echo '</td>';

					if ( $component ) {
						echo '<td class="qm-nowrap">' . esc_html( $component->name ) . '</td>';
					} else {
						echo '<td><em>' . esc_html__( 'Unknown', 'query-monitor' ) . '</em></td>';
					}

					echo '</tr>';

					$first = false;

				}

			}

		}

		echo '</tbody>';
		echo '</table>';
		echo '</div>';

	}

}

function unregister_qm_output_html_php_errors( array $output ) {
    remove_filter( 'qm/outputter/html', 'register_qm_output_html_php_errors', 110, 2 );
    return $output;
}

function register_qmx_output_html_php_errors( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'php_errors' ) ) {
		$output['qmx-php_errors'] = new QMX_Output_Html_PHP_Errors( $collector );
	}
	return $output;
}

?>
