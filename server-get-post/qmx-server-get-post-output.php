<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @property-read QMX_Collector_Server_Get_Post $collector
 */
class QMX_Output_Html_Server_Get_Post extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function output() {
		$data = $this->collector->get_data();

		echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';

			var_dump( $data );

		echo '</div>';
	}

	/**
	 * @param array<string, array<string, mixed>> $menu
	 * @return array<string, array<string, mixed>>
	 */
	public function panel_menu( array $menu ) {
		$menu['server-get-post'] = $this->menu( array(
			'title' => esc_html__( 'SERVER, GET, POST', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-server-get-post',
		) );

		return $menu;
	}

}

add_filter( 'qm/outputter/html', static function ( array $output ) : array {
	if ( $collector = QM_Collectors::get( 'server-get-post' ) ) {
		$output['server-get-post'] = new QMX_Output_Html_Server_Get_Post( $collector );
	}

	return $output;
}, 70 );