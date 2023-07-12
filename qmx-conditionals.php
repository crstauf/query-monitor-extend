<?php
/**
 * Plugin Name: QMX: Additional Conditionals
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/qmx-conditionals.php
 * Description: Additional conditionals for Query Monitor.
 * Version: 1.0.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

add_filter( 'qm/collect/conditionals', static function( array $conditionals ) : array {
	$conditionals = array_merge( $conditionals, array(
		'has_post_thumbnail',
	) );

	/**
	 * WooCommerce
	 *
	 * @link https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-conditional-functions.html
	 */
	if ( class_exists( 'WooCommerce' ) )
		$conditionals = array_merge( $conditionals, array(
			'is_account_page',
			'is_add_payment_method_page',
			'is_cart',
			'is_checkout',
			'is_checkout_pay_page',
			'is_edit_account_page',
			'is_lost_password_page',
			'is_order_received_page',
			'is_product',
			'is_product_category',
			'is_product_tag',
			'is_product_taxonomy',
			'is_shop',
			'is_store_notice_showing',
			'is_view_order_page',
			'is_wc_endpoint_url',
			'is_woocommerce',
		) );

	sort( $conditionals );

	return $conditionals;
} );