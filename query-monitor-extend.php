<?php
/*
Plugin Name: Query Monitor Extend
Plugin URI: 
Description: Enhancements and extensions for the awesome Query Monitor plugin
Version: 0.0.2
Author: Caleb Stauffer
Author URI: http://develop.calebstauffer.com
*/

new css_qm_extend;
class css_qm_extend {

	function __construct() {
		add_filter('qm/collect/conditionals',array(__CLASS__,'add_conditionals'),9999999);
		add_filter('qm/output/menu_class',array(__CLASS__,'adminbar_menu_bg'),9999999);
	}

	public static function adminbar_menu_bg($classes) {
		if (2 > count($classes)) return $classes;

		$num = 0;
		if ($collector = QM_Collectors::get( 'db_queries' )) {
			$expensive = count($collector->get_expensive());
			$num = $num + $expensive;
		}
		if ($collector = QM_Collectors::get( 'php_errors' )) {
			$data = $collector->get_data();
			if (isset($data['errors']))
				foreach ($data['errors'] as $type => $object) {
					$$type = count($data['errors'][$type]);
					$num = $num + $$type;
				}
		}
		if ($collector = QM_Collectors::get( 'http' )) {
			$data = $collector->get_data();
			foreach ($data['errors'] as $type => $object) {
				$$type += count($data['errors'][$type]);
				$num = $num + $$type;
			}
		}

		$colors = array(
			'warning'		=> '#c00',
			'error'			=> '#c00',
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
					$pos = $$class / $num > 0.03 ?: 0.03;
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
		$conds = array_unique($conds);
		sort($conds);
		return $conds;
	}

}

if (!function_exists('is_custom_post_type')) {
	function is_custom_post_type() {
		global $wp_query;

		if ( ! isset( $wp_query ) || !function_exists( 'get_post_type_object' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1' );
			return false;
		}

		$post_obj = $wp_query->get_queried_object();
		$post_type_obj = get_post_type_object($post_obj->post_type);
		return !$post_type_obj->_builtin;
	}
}

?>