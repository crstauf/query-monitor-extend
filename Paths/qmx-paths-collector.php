<?php
/**
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/Paths
 * Description: Query Monitor collector for paths.
 * Version: 1.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

add_action( 'plugin_loaded', 'load_qmx_paths_collector' );

function load_qmx_paths_collector( string $file ) {

	if ( 'query-monitor/query-monitor.php' !== plugin_basename( $file ) )
		return;

	remove_action( 'plugin_loaded', __FUNCTION__ );

	if ( !class_exists( 'QueryMonitor' ) )
		return;

	if ( defined( 'QMX_DISABLE' ) && QMX_DISABLE )
		return;

	class QMX_Collector_Paths extends QM_Collector {

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

			ksort( $this->data['paths'], SORT_FLAG_CASE | SORT_STRING );

		}

	}

	add_filter( 'qm/collectors', static function ( array $collectors ) : array {
		$collectors['paths'] = new QMX_Collector_Paths;
		return $collectors;
	} );

}