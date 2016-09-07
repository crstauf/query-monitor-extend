<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class QMX_Output_Html_Benchmarks extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );

        add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 20 );

        $this->db_query_time = 0;
        $this->db_query_types = array();
	}

	public function output() {

		$data = $this->collector->get_data();

        $included_files = count( get_included_files() );
        $overview_data = QM_Collectors::get( 'overview' )->get_data();

        $show_db_cols = defined( 'SAVEQUERIES' ) && SAVEQUERIES;

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm">';

			echo '<table cellspacing="0" class="qm-sortable">';
				echo '<thead>';
					echo '<tr>';
						echo '<th colspan="' . ( $show_db_cols ? 7 : 5 ) . '">Benchmarks</th>';
					echo '</tr>';
                    echo '<tr>';
                        echo '<th class="qm-num qm-sorted-asc">&nbsp;' . $this->build_sorter() . '</th>';
                        echo '<th>Label' . $this->build_sorter() . '</th>';
                        echo '<th>Page generation time</th>';
                        echo '<th>Memory usage</th>';
                        echo $show_db_cols ? '<th>Database query time</th><th>Database queries</th>' : '';
                        echo '<th>Included files</th>';
				echo '</thead>';
				echo '<tbody>';

                foreach ( $data['benchmarks'] as $row ) {

					if ( array_key_exists( 'db_query_types', $row ) )
	                    foreach ( $row['db_query_types'] as $type_name => $type_count ) {
	                        if ( array_key_exists( $type_name, $this->db_query_types ) )
	                            $type_count += $this->db_query_types[$type_name];
	                        $this->db_query_types[$type_name] = $type_count;
	                    }

                    $db_queries_data = QM_Collectors::get( 'db_queries' )->get_data();

                    $db_query_types = array();
                    foreach ( $this->db_query_types as $type_name => $type_count )
                        $db_query_types[] = sprintf( '%1$s: %2$s', $type_name, number_format_i18n( $type_count ) );

                    echo '<tr>';
                        echo '<td class="qm-num">' . esc_html( ( intval( $row['i'] ) + 1 ) ) . '</td>';

                        echo '<td data-qm-sort-weight="' . esc_attr( $row['label'] ) . '">' . ( $row['label'] ? esc_html( $row['label'] ) : '<span style="color: #999;">' . $row['timestamp'] . '</span>' ) . '</td>';

                        echo '<td>';
                            echo esc_html( number_format_i18n( $row['time'], 4 ) );
                    		echo '<br><span class="qm-info">';
                    		echo esc_html( sprintf(
                    			__( '%1$s%% of %2$ss total', 'query-monitor' ),
                    			number_format_i18n( ( 100 / ( array_key_exists( 'time_taken', $overview_data ) ? $overview_data['time_taken'] : $overview_data['time'] ) ) * $row['time'], 1 ),
                    			number_format_i18n( ( array_key_exists( 'time_taken', $overview_data ) ? $overview_data['time_taken'] : $overview_data['time'] ), 4 )
                    		) );
                    		echo '</span>';
                        echo '</td>';

                        echo '<td>';
                            echo esc_html( sprintf(
                                __( '%s kB', 'query-monitor' ),
                                number_format_i18n( $row['memory'] / 1024 )
                            ) );
                            echo '<br><span class="qm-info">';
                            echo esc_html( sprintf(
                                __( '%1$s%% of %2$ss kB total', 'query-monitor' ),
                                number_format_i18n( ( 100 / $overview_data['memory'] ) * $row['memory'], 1 ),
                                number_format_i18n( ( $overview_data['memory'] / 1024 ) )
                            ) );
                            echo '</span>';
                        echo '</td>';

                        if ( $show_db_cols && array_key_exists( 'db_query_time', $row ) ) {

                            echo '<td>';
                                echo esc_html( number_format_i18n( ( $row['db_query_time'] + $this->db_query_time ), 4 ) );
                                echo '<br><span class="qm-info">';
                        		echo esc_html( sprintf(
                        			__( '%1$s%% of %2$ss total', 'query-monitor' ),
                        			number_format_i18n( ( 100 / $db_queries_data['total_time'] ) * ( $row['db_query_time'] + $this->db_query_time ), 1 ),
                        			number_format_i18n( $db_queries_data['total_time'], 4 )
                        		) );
                            echo '</td>';

                            echo '<td>' . implode( '<br>', array_map( 'esc_html', $db_query_types ) ) . '</td>';

                        } else
							echo '<td>&nbsp;</td><td>&nbsp;</td>';

                        echo '<td>';
                            echo esc_html( $row['included_files'] ) . '<span class="qm-info">/' . $included_files . '</span>';
                        echo '</td>';

                    echo '</tr>';

					if ( array_key_exists( 'db_query_time', $row ) )
                    	$this->db_query_time += $row['db_query_time'];
                }

                echo '</tbody>';
            echo '</table>';

        echo '</div>';
    }

    public function admin_menu( array $menu ) {

        $data = $this->collector->get_data();

        $add = array(
            'title' => sprintf(
                __( 'Benchmarks (%s)', 'query-monitor' ),
                (
                    is_array( $data['benchmarks'] )
                    ? count( $data['benchmarks'] )
                    : 0
                )
            )
        );

        $menu[] = $this->menu( $add );

        return $menu;
    }

}

function register_qmx_output_html_benchmarks( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'qmx-benchmarks' ) )
		$output['qmx-benchmarks'] = new QMX_Output_Html_Benchmarks( $collector );
	return $output;
}

?>
