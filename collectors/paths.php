<?php
/**
 * Paths collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_Paths extends QMX_Collector {

	public $id = 'paths';

	public function name() {
		return __( 'Paths', 'query-monitor-extend' );
	}

	function process() {

		$this->data['paths'] = apply_filters( 'qmx/collector/paths', array(
			'ABSPATH' => ABSPATH,
			'COOKIEPATH' => COOKIEPATH,
			'SITECOOKIEPATH' => SITECOOKIEPATH,
			'DOMAIN_CURRENT_SITE' => defined( 'DOMAIN_CURRENT_SITE' ) ? DOMAIN_CURRENT_SITE : 'undefined',
			'PATH_CURRENT_SITE' => defined( 'PATH_CURRENT_SITE' ) ? PATH_CURRENT_SITE : 'undefined',
			'WP_SITEURL' => defined( 'WP_SITEURL' ) ? WP_SITEURL : 'undefined',
			'site_url()' => site_url(),
			'get_site_url()' => get_site_url(),
			'network_site_url()' => network_site_url(),
			'WP_HOME' => defined( 'WP_HOME' ) ? WP_HOME : 'undefined',
			'home_url()' => home_url(),
			'get_home_url()' => get_home_url(),
			'network_home_url()' => network_home_url(),
			'get_home_path()' => function_exists( 'get_home_path' ) ? get_home_path() : '',
			'WP_CONTENT_URL' => WP_CONTENT_URL,
			'WP_CONTENT_DIR' => WP_CONTENT_DIR,
			'content_url()' => content_url(),
			'WP_PLUGIN_URL' => WP_PLUGIN_URL,
			'WP_PLUGIN_DIR' => WP_PLUGIN_DIR,
			'PLUGINS_COOKIE_PATH' => PLUGINS_COOKIE_PATH,
			'plugins_url()' => plugins_url(),
			'plugin_dir_url( __FILE__ )' => plugin_dir_url( __FILE__ ),
			'plugin_dir_path( __FILE__ )' => plugin_dir_path( __FILE__ ),
			'plugin_basename( __FILE__ )' => plugin_basename( __FILE__ ),
			'WPMU_PLUGIN_DIR' => WPMU_PLUGIN_DIR,
			'WPMU_PLUGIN_URL' => WPMU_PLUGIN_URL,
			'get_theme_root()' => get_theme_root(),
			'get_theme_roots()' => get_theme_roots(),
			'get_theme_root_uri()' => get_theme_root_uri(),
			'get_template_directory()' => get_template_directory(),
			'TEMPLATEPATH' => TEMPLATEPATH,
			'get_template_directory_uri()' => get_template_directory_uri(),
			'get_stylesheet_uri()' => get_stylesheet_uri(),
			'get_stylesheet_directory()' => get_stylesheet_directory(),
			'STYLESHEETPATH' => STYLESHEETPATH,
			'get_stylesheet_directory_uri()' => get_stylesheet_directory_uri(),
			'admin_url()' => admin_url(),
			'get_admin_url()' => get_admin_url(),
			'network_admin_url()' => network_admin_url(),
			'ADMIN_COOKIE_PATH' => ADMIN_COOKIE_PATH,
			'WPINC' => WPINC,
			'includes_url()' => includes_url(),
			'WP_LANG_DIR' => WP_LANG_DIR,
			'BLOGUPLOADDIR' => defined( 'BLOGUPLOADDIR' ) ? BLOGUPLOADDIR : 'undefined',
			'UPLOADBLOGSDIR' => defined( 'UPLOADBLOGSDIR' ) ? UPLOADBLOGSDIR : 'undefined',
			'UPLOADS' => defined( 'UPLOADS' ) ? UPLOADS : 'undefined',
			'wp_upload_dir()' => wp_upload_dir(),
		) );

		ksort( $this->data['paths'] );

	}

}

function register_qmx_collector_paths( array $collectors, QueryMonitorExtend $qmx ) {
	$collectors['paths'] = new QMX_Collector_Paths;
	return $collectors;
}

add_filter( 'qmx/collectors', 'register_qmx_collector_paths', 10, 2 );