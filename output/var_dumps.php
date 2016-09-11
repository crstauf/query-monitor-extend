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

		echo '<span id="' . esc_attr( $this->collector->id() ) . '"></span>';

        if ( array_key_exists( 'vardumps', $data ) && is_array( $data['vardumps'] ) && count( $data['vardumps'] ) )
    		foreach ( $data['vardumps'] as $id => $array ) {

                echo '<div id="' . esc_attr( $this->collector->id() . '-' . $id ) . '" class="qm">';

                echo '<table cellspacing="0">';
        		echo '<thead><tr><td>Var Dump: ' . $array['label'] . '</td></tr></thead>';
                echo '<tbody><tr><td>';

    			QM_Output_Html::output_inner( $array['var'] );

                echo '</td></tr></tbody>';
                echo '</table>';
                echo '</div>';
            }

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
