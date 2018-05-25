<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Output_Html_VarDumps extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );

        add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 200 );
	}

	public function output() {

		$data = $this->collector->get_data();

        if ( array_key_exists( 'vardumps', $data ) && is_array( $data['vardumps'] ) && count( $data['vardumps'] ) )
    		foreach ( $data['vardumps'] as $id => $array ) {

                echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm">';

                echo '<table cellspacing="0">';
        		echo '<thead><tr><th>Var Dump: ' . $array['label'] . '</th></tr></thead>';
                echo '<tbody><tr><td>';

                $var = $this->prepare_output_inner( $array['var'] );
    			QM_Output_Html::output_inner( $var );

                echo '</td></tr></tbody>';
                echo '</table>';
                echo '</div>';
            }

	}

	public function prepare_output_inner( $var ){
		if( ! is_object( $var ) || ! is_array( $var ) ){
			if( is_string( $var) ){
				$var = [ 'string' => $var ];
			} elseif ( is_int( $var ) ){
				$var = [ 'integer' => $var ];
			} elseif ( is_float( $var ) ){
				$var = [ 'float' => $var ];
			} elseif ( is_bool( $var ) ){
				$var = [ 'boolean' => $var ];
			}
		}

		return $var;
	}

    public function admin_menu( array $menu ) {

        $data = $this->collector->get_data();

        $add = array(
            'title' => sprintf(
                __( 'Var Dumps (%s)', 'query-monitor' ),
                (
                    array_key_exists( 'vardumps', $data )
                    && is_array( $data['vardumps'] )
                    ? count( $data['vardumps'] )
                    : 0
                )
            )
        );

        $menu[] = $this->menu( $add );

        return $menu;
    }

}

function register_qmx_output_html_vardumps( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'qmx-var_dumps' ) )
		$output['qmx-var_dumps'] = new QMX_Output_Html_VarDumps( $collector );
	return $output;
}

?>
