<?php
/*
Plugin Name: Query Monitor Extend
Plugin URI:
Description: Enhancements and extensions for the awesome Query Monitor plugin by John Blackbourn
Version: 0.0.3
Author: Caleb Stauffer
Author URI: http://develop.calebstauffer.com
*/

if (!defined('ABSPATH') || !function_exists('add_filter')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

new cssllc_query_monitor_extend;
class cssllc_query_monitor_extend {

	public static $var_dumps = array();

	function __construct() {
		if (class_exists('QM_Collector'))
			foreach ( glob( trailingslashit( dirname( __FILE__ ) ) . 'collectors/*.php' ) as $file )
				include $file;

		// only hook available to load outputters at the right time
		// @TODO: add action to QM in dispatchers/Html.php before_output method
		add_filter('qm/output/absolute_position',array(__CLASS__,'include_outputters'));

		add_filter( 'qm/outputter/html', 'register_cssllc_qmx_output_html_constants', 150, 2 );
		add_filter( 'qm/outputter/html', 'register_cssllc_qmx_output_html_paths', 151, 2 );
		add_filter( 'qm/outputter/html', 'register_cssllc_qmx_output_html_multisite', 152, 2 );
		add_filter( 'qm/outputter/html', 'register_cssllc_qmx_output_html_imagesizes', 153, 2 );
		add_filter( 'qm/outputter/html', 'register_cssllc_qmx_output_html_vardumps', 200, 2 );

		add_filter('qm/collect/conditionals',array(__CLASS__,'add_conditionals'),9999999);
		add_filter('qm/output/menu_class',array(__CLASS__,'adminbar_menu_bg'),9999999);
	}

	public static function include_outputters($absolute) {
		if (class_exists('QM_Output_Html'))
			foreach ( glob( trailingslashit( dirname( __FILE__ ) ) . 'output/*.php' ) as $file )
				include $file;

		return $absolute;
	}

	public static function adminbar_menu_bg($classes) {
		if (2 > count($classes)) return array_merge($classes,array('query-monitor-extend'));
		$classes[] = 'query-monitor-extend';

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
				if (isset($data[$error_type]))
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

		foreach (array(
			'php_errors',
			'http',
		) as $collector_name)
			if ($collector = QM_Collectors::get( $collector_name )) {
				$data = $collector->get_data();
				if (isset($data['errors']))
					foreach ($data['errors'] as $type => $object) {
						if (!isset($$type)) $$type = count($data['errors'][$type]);
						else $$type += count($data['errors'][$type]);
						$num += count($data['errors'][$type]);
					}
			}

		$colors = array(
			'warning'		=> '#c00',
			'error'			=> '#c00',
			'alert'			=> '#f60',
			'notice'		=> '#740',
			'expensive'		=> '#b60',
			'strict'		=> '#3c3c3c',
			'deprecated'	=> '#3c3c3c',
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
		foreach ($styles as $browser => $style)
			$lasts[$browser] = 0;

		foreach ($colors as $class => $color) {
			if (in_array('qm-' . $class,$classes)) {
				$i = array_search($class,$classes);
				foreach ($styles as $browser => $style) {
					if (!isset($$class)) continue;
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
		}

		echo '<style type="text/css">#wpadminbar li#wp-admin-bar-query-monitor {';
		foreach ($styles as $browser => $style)
			echo 'background-image: ' . $browser . '(' . implode(', ',$style) . ') !important;';
		echo '}</style>';

		return $classes;
	}

	public static function add_conditionals($conds) {
		$conds = array_merge($conds,array('is_custom_post_type'));
		if (class_exists('WooCommerce')) {
			$conds = array_merge($conds,array(
				'is_account_page',
				'is_cart',
				'is_checkout',
				'is_product',
				'is_product_category',
				'is_product_tag',
				'is_shop',
				'is_wc_endpoint_url',
				'is_woocommerce',
			));
		}
		$conds = array_unique(apply_filters('qmx/collect/conditionals',$conds));
		sort($conds);
		return $conds;
	}

	public static function register_qm_collector_constants( array $collectors, QueryMonitor $qm ) {
		$collectors['constants'] = new CSS_QM_Collector_Constants;
		return $collectors;
	}

	public static function register_qm_output_html_constants( array $output, QM_Collectors $collectors ) {
		require_once 'output-html.php';
		if ( $collector = QM_Collectors::get( 'constants' ) ) {
			$output['constants'] = new CSS_QM_Output_Html_Constants( $collector );
		}
		return $output;
	}

	public static function register_qm_collector_multisite( array $collectors, QueryMonitor $qm ) {
		$collectors['multisite'] = new CSS_QM_Collector_Multisite;
		return $collectors;
	}

	public static function register_qm_output_html_multisite( array $output, QM_Collectors $collectors ) {
		if ( $collector = QM_Collectors::get( 'multisite' ) ) {
			$output['multisite'] = new CSS_QM_Output_Html_Multisite( $collector );
		}
		return $output;
	}

	public static function register_qm_collector_paths( array $collectors, QueryMonitor $qm ) {
		$collectors['paths'] = new CSS_QM_Collector_Paths;
		return $collectors;
	}

	public static function register_qm_output_html_paths( array $output, QM_Collectors $collectors ) {
		if ( $collector = QM_Collectors::get( 'paths' ) ) {
			$output['paths'] = new CSS_QM_Output_Html_Paths( $collector );
		}
		return $output;
	}

	public static function register_qm_collector_var_dumps( array $collectors, QueryMonitor $qm ) {
		$collectors['vardumps'] = new CSS_QM_Collector_VarDumps;
		return $collectors;
	}

	public static function register_qm_output_html_var_dumps( array $output, QM_Collectors $collectors ) {
		if ( $collector = QM_Collectors::get( 'vardumps' ) ) {
			$output['vardumps'] = new CSS_QM_Output_Html_VarDumps( $collector );
		}
		return $output;
	}

	public static function register_qm_collector_image_sizes( array $collectors, QueryMonitor $qm ) {
		$collectors['imagesizes'] = new CSS_QM_Collector_ImageSizes;
		return $collectors;
	}

	public static function register_qm_output_html_image_sizes( array $output, QM_Collectors $collectors ) {
		if ( $collector = QM_Collectors::get( 'imagesizes' ) ) {
			$output['imagesizes'] = new CSS_QM_Output_Html_ImageSizes( $collector );
		}
		return $output;
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

if ( !function_exists('QM_dump') ) {
	function QM_dump( $label, $var, $single_table = false ) {
		cssllc_query_monitor_extend::$var_dumps[time() . '_' . $label] = array( $var, $single_table );
	}
}

?>
