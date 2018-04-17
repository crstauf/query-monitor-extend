<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class QMX_Output_Html_Assets extends QM_Output_Html_Assets {

	const QM_version = '2.13.2';

	public function output() {

		$data = $this->collector->get_data();

		if ( empty( $data['raw'] ) ) {
			return;
		}

		echo '<div id="' . esc_attr( $this->collector->id() ) . '">';

		$position_labels = array(
			'missing' => __( 'Missing', 'query-monitor' ),
			'broken'  => __( 'Broken Dependencies', 'query-monitor' ),
			'header'  => __( 'Header', 'query-monitor' ),
			'footer'  => __( 'Footer', 'query-monitor' ),
		);

		$type_labels = array(
			'scripts' => array(
				'singular' => __( 'Script', 'query-monitor' ),
				'plural'   => __( 'Scripts', 'query-monitor' ),
			),
			'styles' => array(
				'singular' => __( 'Style', 'query-monitor' ),
				'plural'   => __( 'Styles', 'query-monitor' ),
			),
		);

		foreach ( $type_labels as $type => $type_label ) {

			echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '-' . esc_attr( $type ) . '">';
			echo '<table cellspacing="0">';
			echo '<caption>' . esc_html( $type_label['plural'] ) . '</caption>';
			echo '<thead>';
			echo '<tr>';
			echo '<th scope="col">' . esc_html__( 'Position', 'query-monitor' ) . '</th>';
			echo '<th scope="col">' . esc_html( $type_label['singular'] ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Dependencies', 'query-monitor' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Dependents', 'query-monitor' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Version', 'query-monitor' ) . '</th>';
			echo '<th>' . esc_html__( 'Extras', 'query-monitor' ) . '</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			foreach ( array(
				'missing',
				'broken',
				'header',
				'footer',
			) as $position ) {

				if ( isset( $data[ $position ][ $type ] ) ) {
					$this->dependency_rows( $data[ $position ][ $type ], $data['raw'][ $type ], $position_labels[ $position ], $type );
				}

			}

			echo '</tbody>';
			echo '</table>';
			echo '</div>';

		}

		echo '</div>';

	}

	protected function dependency_rows( array $handles, WP_Dependencies $dependencies, $label, $type ) {

		$first = true;

		if ( empty( $handles ) ) {
			echo '<tr>';
			echo '<td class="qm-nowrap">' . esc_html( $label ) . '</td>';
			echo '<td colspan="6"><em>' . esc_html__( 'none', 'query-monitor' ) . '</em></td>';
			echo '</tr>';
			return;
		}

		foreach ( $handles as $handle ) {

			if ( in_array( $handle, $dependencies->done ) ) {
				echo '<tr data-qm-subject="' . esc_attr( $type . '-' . $handle ) . ' ">';
			} else {
				echo '<tr data-qm-subject="' . esc_attr( $type . '-' . $handle ) . ' " class="qm-warn">';
			}

			if ( $first ) {
				$rowspan = count( $handles );
				echo '<th scope="row" rowspan="' . esc_attr( $rowspan ) . '" class="qm-nowrap">' . esc_html( $label ) . '</th>';
			}

			$this->dependency_row( $dependencies->query( $handle ), $dependencies, $type );

			echo '</tr>';
			$first = false;
		}

	}

	protected function dependency_row( _WP_Dependency $dependency, WP_Dependencies $dependencies, $type ) {

		if ( empty( $dependency->ver ) ) {
			$ver = '';
		} else {
			$ver = $dependency->ver;
		}

		$loader = rtrim( $type, 's' );

		/**
		 * Filter the asset loader source.
		 *
		 * The variable {$loader} can be either 'script' or 'style'.
		 *
		 * @param string $src    Script or style loader source path.
		 * @param string $handle Script or style handle.
		 */
		$source = apply_filters( "{$loader}_loader_src", $dependency->src, $dependency->handle );

		if ( is_wp_error( $source ) ) {
			$src = $source->get_error_message();
			if ( ( $error_data = $source->get_error_data() ) && isset( $error_data['src'] ) ) {
				$src .= ' (' . $error_data['src'] . ')';
			}
		} elseif ( empty( $source ) ) {
			$src = '';
		} else {
			$src = $source;
		}

		$dependents = $this->collector->get_dependents( $dependency, $dependencies );
		$deps = $dependency->deps;
		sort( $deps );

		foreach ( $deps as & $dep ) {
			if ( ! $dependencies->query( $dep ) ) {
				/* translators: %s: Script or style dependency name */
				$dep = sprintf( __( '%s (missing)', 'query-monitor' ), $dep );
			}
		}

		$this->type = $type;

		$highlight_deps       = array_map( array( $this, '_prefix_type' ), $deps );
		$highlight_dependents = array_map( array( $this, '_prefix_type' ), $dependents );

		echo '<th scope="row" class="qm-wrap">' . esc_html( $dependency->handle ) . '<br><span class="qm-info">&nbsp;';
		if ( is_wp_error( $source ) ) {
			printf( '<span class="qm-warn">%s</span>',
				esc_html( $src )
			);
		} else {
			echo esc_html( $src );
		}
		echo '</span></td>';
		echo '<td class="qm-nowrap qm-highlighter" data-qm-highlight="' . esc_attr( implode( ' ', $highlight_deps ) ) . ' "><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $deps ) ) . '</li></ul></td>';
		echo '<td class="qm-nowrap qm-highlighter" data-qm-highlight="' . esc_attr( implode( ' ', $highlight_dependents ) ) . ' "><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $dependents ) ) . '</li></ul></td>';
		echo '<td>' . esc_html( $ver ) . '</td>';
		echo '<td class="qm-has-inner">';
			if ( is_array( $dependency->extra ) && count ( $dependency->extra ) ) {
				echo '<table cellspacing="0" class="qm-inner"' . ( count( $deps ) > count( $dependency->extra ) || count( $dependents ) > count( $dependency->extra ) ? ' style="border-bottom-style: solid !important; border-bottom-width: 1px;"' : '' ) . '>';
					foreach ( $dependency->extra as $key => $value ) {
						echo '<tr>';
							echo '<td' . ( !is_array( $value ) && !is_object( $value ) ? ' colspan="2"' : '' ) . ' style="background: transparent !important;">' . esc_html( $key ) . '</td>';
							if ( is_array( $value ) )
								echo '<td>' . count( $value ) . '</td>';
							else if ( is_object( $value ) )
								echo '<td>' . count( get_object_vars( $value ) ) . '</td>';
						echo '</tr>';
					}
				echo '</table>';
			}
		echo '</td>';

	}

    public function get_relative_src( $src ) {
        $siteurl = get_bloginfo( 'url' );
        $siteurl = str_replace( array( 'https://', 'http://' ), '', $siteurl );

        if (
            (
                false !== stripos( $src, 'http://' )
                || false !== stripos( $src, 'https://' )
            )
            && false === stripos( $src, $siteurl )
        )
            return false;

        return preg_replace( '|^(https?:)?//' . $siteurl . '(/?.*)|i', '$2', $src );
    }

    public function get_filesize( $src ) {

        $relative = $this->get_relative_src( $src );

        if (false === $relative || !file_exists( ABSPATH . $relative ) || !is_file( ABSPATH . $relative ) )
            return false;

        return number_format_i18n( filesize( ABSPATH . $relative ) / 1024, 2) . ' KB';

    }

}

function unregister_qm_output_html_assets( array $output ) {
    remove_filter( 'qm/outputter/html', 'register_qm_output_html_assets', 80, 2 );
    return $output;
}

function register_qmx_output_html_assets( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'assets' ) ) {
		$output['qmx-assets'] = new QMX_Output_Html_Assets( $collector );
	}
	return $output;
}

?>
