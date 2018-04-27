<?php
/**
 * The main Query Monitor Extend plugin class.
 *
 * @package query-monitor-extend
 */

class QueryMonitorExtend extends QMX_Plugin {

	protected function __construct( $file ) {

		add_action( 'plugins_loaded',    array( &$this, 'action__plugins_loaded'    ) );
		add_action( 'shutdown',          array( &$this, 'action__shutdown'          ), -1 );

		add_filter( 'plugin_row_meta',         array( &$this, 'filter__plugin_row_meta'         ), 10, 2 );
		add_filter( 'qm/outputter/html',       array( &$this, 'filter__qm_outputter_html'       ) );
		add_filter( 'qm/collect/conditionals', array( &$this, 'filter__qm_collect_conditionals' ) );

		# Parent setup:
		parent::__construct( $file );

		$collectors = array();
		foreach ( glob( $this->plugin_path( 'collectors/*.php' ) ) as $file ) {
			$key = basename( $file, '.php' );
			$collectors[$key] = $file;
		}

		foreach ( apply_filters( 'qmx/built-in-collectors', $collectors ) as $file ) {
			include $file;
		}

	}

	function action__plugins_loaded() {

		# Register additional collectors:
		foreach ( apply_filters( 'qmx/collectors', array(), $this ) as $collector ) {
			QMX_Collectors::add( $collector );
		}

	}

	function action__shutdown() {
		global $qm_dir;

		require_once $qm_dir . '/output/Html.php';
		require_once $this->plugin_path( 'output/Html.php' );

		foreach ( glob( $this->plugin_path( 'output/html/*.php' ) ) as $file ) {
			require_once $file;
		}

	}

	function filter__plugin_row_meta( $meta, $file ) {
		if ( 'query-monitor-extend/query-monitor-extend.php' !== $file )
			return $meta;

		$first = array_shift( $meta );
		array_unshift(
			$meta,
			$first,
			'Tested up to <a href="https://wordpress.org/plugins/query-monitor/" target="_blank" rel="noopener">Query Monitor</a> <a href="https://github.com/johnbillion/query-monitor/releases/tag/3.0.0" target="_blank" rel="noopener">3.0</a>'
		);

		return $meta;
	}

	function filter__qm_outputter_html( $outputters ) {

		return apply_filters( 'qmx/outputter/html', array() );

	}

	function filter__qm_collect_conditionals( $conds ) {

		$conds = array_merge( $conds, array(
			'is_cart',
			'is_checkout',
			'is_shop',
			'is_woocommerce',
			'is_product_category',
			'is_product_tag',
			'is_product',
			'is_account_page',
			'is_wc_endpoint_url',
			'has_post_thumbnail',
		) );

		sort( $conds );

		return $conds;

	}

	public static function init( $file = null ) {

		static $instance = null;

		if ( ! $instance ) {
			$instance = new QueryMonitorExtend( $file );
		}

		return $instance;

	}

}