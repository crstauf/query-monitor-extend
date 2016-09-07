<?php
/*
Plugin Name: Query Monitor Extend
Plugin URI: https://github.com/crstauf/query-monitor-extend
Description: Enhancements and extensions for the awesome Query Monitor plugin by John Blackbourn
Version: 0.0.3 | QM 2.12.0
Author: Caleb Stauffer
Author URI: http://develop.calebstauffer.com
*/

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if (
	!class_exists( 'QM_Activation' )
	|| ( defined( 'QM_DISABLED' ) && QM_DISABLED )
	|| (defined ( 'QMX_DISABLED' ) && QMX_DISABLED )
)
	return;

new query_monitor_extend;
class query_monitor_extend {

	public static $var_dumps = array();

	private static $highlight_suppresseds = false;

	function __construct() {
		if (class_exists('QM_Collector'))
			foreach ( glob( trailingslashit( dirname( __FILE__ ) ) . 'collectors/*.php' ) as $file )
				include $file;

		if ( defined( 'QMX_HIGHLIGHT_SUPPRESSEDS' ) && QMX_HIGHLIGHT_SUPPRESSEDS )
			self::$highlight_suppresseds = true;

		add_filter( 'qm/outputter/html', array( __CLASS__, 'include_outputters' ), 0 );
		add_filter( 'qmx/collect/constants', array( __CLASS__, 'filter_qmx_collect_constants' ) );
		add_filter( 'qm/collect/conditionals', array( __CLASS__, 'filter_qm_collect_conditionals' ), 9999999 );

		add_action( ( is_admin() ? 'admin' : 'wp' ) . '_enqueue_scripts', array( __CLASS__, 'action_enqueue_scripts' ) );

		add_filter( 'qm/output/menu_class', array( __CLASS__, 'filter_qm_output_menu_class' ), 9999999 );

		add_filter( 'qm/outputter/html', 'unregister_qm_output_html_assets', 79 );
		add_filter( 'qm/outputter/html', 'unregister_qm_output_html_transients', 99, 2 );
		add_filter( 'qm/outputter/html', 'unregister_qm_output_html_php_errors', 109 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_benchmarks', 15, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_assets', 80, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_transients', 100, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_php_errors', 110, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_includedfiles', 119, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_time', 149, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_constants', 150, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_paths', 151, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_multisite', 152, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_imagesizes', 153, 2 );
		add_filter( 'qm/outputter/html', 'register_qmx_output_html_vardumps', 200, 2 );
	}

	public static function include_outputters( $output ) {
		if (class_exists('QM_Output_Html'))
			foreach ( glob( trailingslashit( dirname( __FILE__ ) ) . 'output/*.php' ) as $file )
				include $file;

		return $output;
	}

	public static function filter_qm_output_menu_class( $classes ) {
		if ( in_array( 'qm-all-clear', $classes ) )
			return array_merge( $classes, array( 'qmx' ) );

		$classes[] = 'qmx';
		if ( self::$highlight_suppresseds )
			$classes[] = 'qmx-highlight-suppresseds';

		$num = 0;

		if ($collector = QM_Collectors::get( 'db_queries' )) {
			if (false !== $collector->get_expensive())
				$num += $expensive = count($collector->get_expensive());
			if (false !== $collector->get_errors()) {
				$error = count($collector->get_errors());
				$num += $error;
			}
		}

		if ($collector = QM_Collectors::get( 'assets' )) {
			$data = $collector->get_data();
			foreach (array(
				'missing',
				'broken',
			) as $error_type)
				if (array_key_exists($error_type,$data) && is_array($data[$error_type]) && count($data[$error_type]))
					foreach (array(
						'scripts',
						'styles',
					) as $type)
						if (isset($data[$error_type][$type])) {
							if (!isset($error)) $error = count($data[$error_type][$type]);
							else $error += count($data[$error_type][$type]);
							$num += count($data[$error_type][$type]);
						}
		}

		foreach ( array(
			'php_errors',
			'http',
		) as $collector_name )
			if ( $collector = QM_Collectors::get( $collector_name ) ) {
				$data = $collector->get_data();
				if ( is_array( $data ) && array_key_exists( 'errors', $data ) )
					foreach ( $data['errors'] as $type => $objects ) {
						$count_type = str_replace( '-', '_', $type );
						if ( !isset( $$count_type ) ) $$count_type = count( $objects );
						else $$count_type += count( $objects );
						$num += count( $objects );
					}
			}

		$colors = array(
			'warning'               => '#c00',
			'error'                 => '#c00',
			'warning_suppressed'    => '#c00',
			'notice_suppressed'     => '#740',
			'notice'                => '#740',
			'alert'                 => '#f60',
			'expensive'             => '#b60',
			'strict'                => '#3c3c3c',
			'strict_suppressed'     => '#3c3c3c',
			'deprecated'            => '#3c3c3c',
			'deprecated_suppressed' => '#3c3c3c',
		);
		$styles = array(
			'-ms-linear-gradient'		=> array('left'),
			'-moz-linear-gradient'		=> array('left'),
			'-o-linear-gradient'		=> array('left'),
			'-webkit-gradient'			=> array('linear','left top','right top'),
			'-webkit-linear-gradient'	=> array('top'),
			'linear-gradient'			=> array('to right'),
		);

		$lasts = array();
		foreach ( $styles as $browser => $style )
			$lasts[$browser] = 0;

		foreach ( $colors as $class => $color ) {
			if (
				isset( $$class )
				&& (
					in_array( 'qm-' . $class, $classes )
					|| (
						self::$highlight_suppresseds
						&& false !== stripos( $class, '_suppressed' )
					)
				)
			)
				foreach ( $styles as $browser => $style ) {
					$pos = $$class / $num > 0.03 ? $$class / $num : 0.03;
					if ('-webkit-gradient' == $browser) {
						$style[] = 'color-stop(' . $lasts[$browser] . ', ' . $color . ')';
						$style[] = 'color-stop(' . ($pos + $lasts[$browser]) . ', ' . $color . ')';
					} else {
						$style[] = $color . ' ' . ($lasts[$browser] * 100) . '%';
						$style[] = $color . ' ' . (($pos + $lasts[$browser]) * 100) . '%';
					}
					$lasts[$browser] = $$class / $num;
					$styles[$browser] = $style;
				}
		}

		echo '<style type="text/css">' .
			'#wpadminbar li#wp-admin-bar-query-monitor.qmx {';

			foreach ($styles as $browser => $style)
				echo 'background-image: ' . $browser . '(' . implode(', ',$style) . ') !important;';

			echo '}' .
		'</style>';

		return $classes;
	}

	public static function filter_qmx_collect_constants($constants) {
		if ( class_exists( 'WooCommerce' ) )
			$constants = array_merge( $constants, array(
				'SHOP_IS_ON_FRONT',
				'WC_TEMPLATE_DEBUG_MODE',
				'WC_ROUNDING_PRECISION',
			) );
		return $constants;
	}

	public static function filter_qm_collect_conditionals($conds) {
		$conds = array_merge($conds,array('is_custom_post_type'));
		if (class_exists('WooCommerce')) {
			$conds = array_merge($conds,array(
				'is_account_page',
				'is_cart',
				'is_checkout',
				'is_checkout_pay_page',
				'is_product',
				'is_product_taxonomy',
				'is_product_category',
				'is_product_tag',
				'is_shop',
				'is_wc_endpoint_url',
				'is_woocommerce',
				'is_view_order_page',
				'is_edit_account_page',
				'is_order_received_page',
				'is_add_payment_method_page',
				'is_lost_password_page',
				'is_store_notice_showing',
			));
		}
		$conds = array_unique(apply_filters('qmx/collect/conditionals',$conds));
		sort($conds);
		return $conds;
	}

	public static function action_enqueue_scripts() {
		wp_enqueue_style(  'query-monitor-extend', plugin_dir_url( __FILE__ ) . 'styles.css', array( 'query-monitor' ), 'init' );
		wp_enqueue_script( 'query-monitor-extend', plugin_dir_url( __FILE__ ) . 'scripts.js', array( 'jquery', 'query-monitor' ), 'init' );
	}

	public static function get_format_value( $value, $is_constant = false ) {
		if ( true === $is_constant && !defined( $value ) )
			return 'undefined';
		else if ( defined( $value ) )
			$value = constant( $value );

		if ( is_bool( $value ) ) {
			if ( true === $value ) return 'true';
			return 'false';
		}
		if ( is_array( $value ) || is_object( $value ) )
			return gettype( $value );

		return esc_attr( $value );
	}

}

if ( !function_exists( 'is_custom_post_type' ) ) {
	function is_custom_post_type() {
		global $wp_query;

		if ( !isset( $wp_query ) || !function_exists( 'get_post_type_object' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1' );
			return false;
		}

		if ( !$wp_query->is_singular() ) return false;

		$post_obj = $wp_query->get_queried_object();
		$post_type_obj = get_post_type_object($post_obj->post_type);
		return !$post_type_obj->_builtin;
	}
}

?>
