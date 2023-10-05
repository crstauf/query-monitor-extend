<?php declare(strict_types=1);

namespace QMX;

use QM_Collectors;
use QMX\Collector\ACF;
use QMX\Collector\Constants;
use QMX\Collector\Files;
use QMX\Collector\Heartbeat;
use QMX\Collector\ImageSizes;
use QMX\Collector\Paths;
use QMX\Collector\Time;

defined( 'WPINC' ) || die();

class Plugin {

	public static function boot() : void {

if ( constant( 'QM_DISABLED' ) ) {
	return;
}
if ( ! class_exists( 'QueryMonitor' ) || did_action( 'qm/cease' ) ) {
	return;
}

		QM_Collectors::add( new ACF() );
		QM_Collectors::add( new Constants() );
		QM_Collectors::add( new Files() );
		QM_Collectors::add( new Heartbeat() );
		QM_Collectors::add( new ImageSizes() );
		QM_Collectors::add( new Paths() );
		QM_Collectors::add( new Time() );

		add_filter( 'qm/outputter/html', static function ( array $output ) : array {
			if ( $collector = QM_Collectors::get( 'constants' ) ) {
				$output['constants'] = new \QMX\Output\Html\Constants( $collector );
			}

			return $output;
		}, 70 );
	}

	public static function add_plugin_links() : void {

/**
 * Filter: plugin_row_meta
 *
 * - add "Tested up to" notice
 * - add "Requires QM" notice
 *
 * @param array $meta
 * @param string $file
 * @return string[]
 */
add_filter( 'plugin_row_meta', static function ( array $meta, string $file ) : array {
	if ( 'query-monitor-extend/query-monitor-extend.php' !== $file ) {
		return $meta;
	}

	$first = array_shift( $meta );

	array_unshift(
		$meta,
		$first,
		sprintf(
			'Tested up to <a href="%1$s" rel="noopener noreferrer">Query Monitor</a> <a href="%2$s%3$s" rel="noopener noreferrer">%3$s</a>',
			'https://wordpress.org/plugins/query-monitor/',
			'https://github.com/johnbillion/query-monitor/releases/tag/',
			QMX_TESTED_WITH_QM,
		)
	);

	if ( class_exists( 'QueryMonitor' ) ) {
		return $meta;
	}

	$first = array_shift( $meta );

	array_unshift(
		$meta,
		$first,
		sprintf(
			'Requires <a href="%1$s" rel="noopener noreferrer">Query Monitor</a>',
			'https://wordpress.org/plugins/query-monitor/'
		)
	);

	return $meta;
}, 10, 2 );

	}

	public static function add_wc_conditionals() : void {

add_filter( 'qm/collect/conditionals', static function ( array $conditionals ) : array {
	$conditionals = array_merge( $conditionals, array(
		'has_post_thumbnail',
	) );

	/**
	 * WooCommerce
	 *
	 * @link https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-conditional-functions.html
	 */
	if ( class_exists( 'WooCommerce' ) ) {
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
	}

	sort( $conditionals );

	return $conditionals;
} );

	}

}
