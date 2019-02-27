<?php
/**
 * Output class to adjust color of admin bar item.
 */

class QMX_Admin_Bar {

	protected $class_to_color = array(
		     'alert' => '#f60',
		     'error' => '#c00',
		    'notice' => '#740',
		    'strict' => '#3c3c3c',
		   'warning' => '#c00',
		 'expensive' => '#b60',
		'deprecated' => '#3c3c3c',
	);

	protected function __construct() {

		$this->collectors = array(
			'assets_scripts' => QM_Collectors::get( 'assets_scripts' ),
			'assets_styles'  => QM_Collectors::get( 'assets_styles'  ),
			'db_queries'     => QM_Collectors::get( 'db_queries'     ),
			'http'           => QM_Collectors::get( 'http'           ),
			'php_errors'     => QM_Collectors::get( 'php_errors'     ),
		);

		add_filter( 'qm/output/menu_class', array( $this, 'filter__qm_output_menu_class' ) );

		$this->process_and_print_styles();

	}

	public static function get_instance() {
		static $_instance = null;

		if ( is_null( $_instance ) )
			$_instance = new self;

		return $_instance;
	}

	function filter__qm_output_menu_class( $classes ) {
		return $classes;
	}

	function process_and_print_styles() {
		$colors = array(
			   '#c00' => 0,
			   '#f60' => 0,
			   '#b60' => 0,
			   '#740' => 0,
			'#3c3c3c' => 0,
		);

		// count PHP errors
		$data = $this->collectors['php_errors']->get_data();
		if ( !empty( $data['errors'] ) )
			foreach ( $data['errors'] as $type => $errors )
				$colors[$this->class_to_color[$type]] = count( $errors );

		foreach ( array( 'assets_styles', 'assets_scripts' ) as $assets ) {
			// count broken assets
			$data = $this->collectors[$assets]->get_data();

			if ( !empty( $data['broken'] ) )
				$colors[$this->class_to_color['error']] += count( $data['broken'] );

			// count missing assets
			if ( !empty( $data['missing'] ) )
				$colors[$this->class_to_color['error']] += count( $data['missing'] );
		}

		// count database query errors
		$data = $this->collectors['db_queries']->get_errors();
		if ( !empty( $data ) )
			$colors[$this->class_to_color['error']] += count( $data );

		// count expensive database queries
		$data = $this->collectors['db_queries']->get_expensive();
		if ( !empty( $data ) )
			$colors[$this->class_to_color['expensive']] += count( $data );

		// count HTTP alerts
		$data = $this->collectors['http']->get_data();
		if ( !empty( $data['errors']['alert'] ) )
			$colors[$this->class_to_color['alert']] += count( $data['errors']['alert'] );

		// count HTTP warnings
		if ( !empty( $data['errors']['warning'] ) )
			$colors[$this->class_to_color['warning']] += count( $data['errors']['warning'] );

		$colors = array_filter( $colors );
		$total  = array_sum(    $colors );

		if ( QueryMonitorExtend::is_debugging() )
			error_log( print_r( $colors, true ) );

		$previous = 0;
		$gradient = array();

		foreach ( $colors as $color => $count ) {
			$percentage = floor( ( $count / $total ) * 100 );
			$gradient[] = $color . ' ' . $previous . '%';
			$gradient[] = $color . ' ' . min( 100, $percentage + $previous ) . '%';
			$previous = $percentage + $previous;
		}
		?>

		<style type="text/css">
			#wp-admin-bar-query-monitor {
				background: -webkit-linear-gradient(     left, <?php echo implode( ', ', $gradient ) ?> ) !important;
				background:    -moz-linear-gradient(     left, <?php echo implode( ', ', $gradient ) ?> ) !important;
				background:         linear-gradient( to right, <?php echo implode( ', ', $gradient ) ?> ) !important;
			}
		</style>

		<?php
	}

}

function register_qmx_output_html_admin_bar( array $output ) {
	if ( QM_Dispatchers::get( 'html' )->is_active() )
		QMX_Admin_Bar::get_instance();

	return $output;
}

add_filter( 'qmx/outputter/html', 'register_qmx_output_html_admin_bar', 70 );
