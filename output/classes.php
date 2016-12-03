<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class QMX_Output_Html_Classes extends QM_Output_Html {

	const cols = 6;

	private static $classes = array();

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 111 );
	}

	public function output() {

		$data['classes'] = self::$classes = $classes = get_declared_classes();
		$i = 0;

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm">';

			echo '<table cellspacing="0">';
				echo '<caption>Registered Classes</caption>';
				echo '<tbody>';

					sort( $data['classes'], SORT_STRING | SORT_FLAG_CASE );

					foreach ( $data['classes'] as $class ) {
						$i++;

						if ( 1 === $i % self::cols )
							echo '<tr>';

						$reflector = new ReflectionClass( $class );

						echo '<td class="qm-ltr"">' .
							esc_html( $class ) . '<br />' .
							( false !== $reflector && false !== $reflector->getFileName() && !empty( $reflector->getFileName() ) ? '&nbsp;<span class="qm-info" title="' . esc_attr( $reflector->getFileName() ) . '">' . basename( $reflector->getFileName() ) . '</span><br />' : '' ) . '<br />' .
						'</td>';

						if ( 0 === $i % self::cols )
							echo '</tr>';

					}

				echo '</tbody>';
				echo '<tfoot>';
					echo '<tr>' .
						'<td colspan="' . self::cols . '" style="text-align: right !important;">Count: ' . count($data['classes']) . '</td>' .
					'</tr>';
				echo '</tfoot>';
			echo '</table>';

		echo '</div>';

	}

	public function admin_menu( array $menu ) {
		$classes = array();

		$add = array(
            'title' => sprintf(
                __( 'Registered Classes (%s)', 'query-monitor' ),
                (
                    is_array( self::$classes )
                    ? count( self::$classes )
                    : 0
                )
            )
        );

		$menu[] = $this->menu( $add );

		return $menu;
	}

}

function register_qmx_output_html_classes( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'qmx-classes' ) )
		$output['qmx-classes'] = new QMX_Output_Html_Classes( $collector );
	return $output;
}

?>
