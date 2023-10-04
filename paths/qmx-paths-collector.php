<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @extends QM_DataCollector<QMX_Data_Paths>
 * @property-write QMX_Data_Paths $data
 */
class QMX_Collector_Paths extends QM_DataCollector {

	public $id = 'paths';

	public function name() : string {
		return __( 'Paths', 'query-monitor-extend' );
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-paths-data.php';
		return new QMX_Data_Paths();
	}

	public function process() {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$this->data->paths = apply_filters( 'qmx/collector/paths', array(
			'ABSPATH'                        => defined( 'ABSPATH' ) ? constant( 'ABSPATH' ) : 'undefined',
			'COOKIEPATH'                     => defined( 'COOKIEPATH' ) ? constant( 'COOKIEPATH' ) : 'undefined',
			'SITECOOKIEPATH'                 => defined( 'SITECOOKIEPATH' ) ? constant( 'SITECOOKIEPATH' ) : 'undefined',
			'DOMAIN_CURRENT_SITE'            => defined( 'DOMAIN_CURRENT_SITE' ) ? constant( 'DOMAIN_CURRENT_SITE' ) : 'undefined',
			'PATH_CURRENT_SITE'              => defined( 'PATH_CURRENT_SITE' ) ? constant( 'PATH_CURRENT_SITE' ) : 'undefined',
			'WP_SITEURL'                     => defined( 'WP_SITEURL' ) ? constant( 'WP_SITEURL' ) : 'undefined',
			'site_url()'                     => site_url(),
			'get_site_url()'                 => get_site_url(),
			'network_site_url()'             => network_site_url(),
			'WP_HOME'                        => defined( 'WP_HOME' ) ? constant( 'WP_HOME' ) : 'undefined',
			'home_url()'                     => home_url(),
			'get_home_url()'                 => get_home_url(),
			'network_home_url()'             => network_home_url(),
			'get_home_path()'                => function_exists( 'get_home_path' ) ? get_home_path() : 'undefined',
			'WP_CONTENT_URL'                 => defined( 'WP_CONTENT_URL' ) ? constant( 'WP_CONTENT_URL' ) : 'undefined',
			'WP_CONTENT_DIR'                 => defined( 'WP_CONTENT_DIR' ) ? constant( 'WP_CONTENT_DIR' ) : 'undefined',
			'content_url()'                  => content_url(),
			'WP_PLUGIN_URL'                  => defined( 'WP_PLUGIN_URL' ) ? constant( 'WP_PLUGIN_URL' ) : 'undefined',
			'WP_PLUGIN_DIR'                  => defined( 'WP_PLUGIN_DIR' ) ? constant( 'WP_PLUGIN_DIR' ) : 'undefined',
			'PLUGINS_COOKIE_PATH'            => defined( 'PLUGINS_COOKIE_PATH' ) ? constant( 'PLUGINS_COOKIE_PATH' ) : 'undefined',
			'plugins_url()'                  => plugins_url(),
			'plugin_dir_url( __FILE__ )'     => plugin_dir_url( __FILE__ ),
			'plugin_dir_path( __FILE__ )'    => plugin_dir_path( __FILE__ ),
			'plugin_basename( __FILE__ )'    => plugin_basename( __FILE__ ),
			'WPMU_PLUGIN_DIR'                => defined( 'WPMU_PLUGIN_DIR' ) ? constant( 'WPMU_PLUGIN_DIR' ) : 'undefined',
			'WPMU_PLUGIN_URL'                => defined( 'WPMU_PLUGIN_URL' ) ? constant( 'WPMU_PLUGIN_URL' ) : 'undefined',
			'get_theme_root()'               => get_theme_root(),
			'get_theme_roots()'              => get_theme_roots(),
			'get_theme_root_uri()'           => get_theme_root_uri(),
			'get_template_directory()'       => get_template_directory(),
			'TEMPLATEPATH'                   => defined( 'TEMPLATEPATH' ) ? constant( 'TEMPLATEPATH' ) : 'undefined',
			'get_template_directory_uri()'   => get_template_directory_uri(),
			'get_stylesheet_uri()'           => get_stylesheet_uri(),
			'get_stylesheet_directory()'     => get_stylesheet_directory(),
			'STYLESHEETPATH'                 => defined( 'STYLESHEETPATH' ) ? constant( 'STYLESHEETPATH' ) : 'undefined',
			'get_stylesheet_directory_uri()' => get_stylesheet_directory_uri(),
			'admin_url()'                    => admin_url(),
			'get_admin_url()'                => get_admin_url(),
			'network_admin_url()'            => network_admin_url(),
			'ADMIN_COOKIE_PATH'              => defined( 'ADMIN_COOKIE_PATH' ) ? constant( 'ADMIN_COOKIE_PATH' ) : 'undefined',
			'WPINC'                          => defined( 'WPINC' ) ? constant( 'WPINC' ) : 'undefined',
			'includes_url()'                 => includes_url(),
			'WP_LANG_DIR'                    => defined( 'WP_LANG_DIR' ) ? constant( 'WP_LANG_DIR' ) : 'undefined',
			'BLOGUPLOADDIR'                  => defined( 'BLOGUPLOADDIR' ) ? constant( 'BLOGUPLOADDIR' ) : 'undefined',
			'UPLOADBLOGSDIR'                 => defined( 'UPLOADBLOGSDIR' ) ? constant( 'UPLOADBLOGSDIR' ) : 'undefined',
			'UPLOADS'                        => defined( 'UPLOADS' ) ? constant( 'UPLOADS' ) : 'undefined',
			'wp_upload_dir()'                => wp_upload_dir(),
			'get_theme_file_path()'          => get_theme_file_path(),
			'get_theme_file_uri()'           => get_theme_file_uri(),
		) );

		if ( defined( 'WP_DEBUG_LOG' ) && is_string( constant( 'WP_DEBUG_LOG' ) ) ) {
			$this->data->paths['WP_DEBUG_LOG'] = constant( 'WP_DEBUG_LOG' );
		}

		ksort( $this->data->paths, SORT_FLAG_CASE | SORT_STRING );
	}

	public function get_concerned_filters() {
		return array(
			'admin_url',
			'content_url',
			'home_url',
			'includes_url',
			'plugins_url',
			'network_admin_url',
			'network_home_url',
			'network_site_url',
			'site_url',
			'stylesheet_directory',
			'stylesheet_directory_uri',
			'stylesheet_uri',
			'template_directory',
			'template_directory_uri',
			'theme_file_path',
			'theme_file_uri',
			'theme_root',
			'theme_root_uri',
			'upload_dir',
		);
	}

	public function get_concerned_constants() {
		return array(
			'ABSPATH',
			'COOKIEPATH',
			'SITECOOKIEPATH',
			'DOMAIN_CURRENT_SITE',
			'PATH_CURRENT_SITE',
			'WP_SITEURL',
			'WP_HOME',
			'WP_CONTENT_URL',
			'WP_CONTENT_DIR',
			'WP_PLUGIN_URL',
			'WP_PLUGIN_DIR',
			'PLUGINS_COOKIE_PATH',
			'WPMU_PLUGIN_DIR',
			'WPMU_PLUGIN_URL',
			'TEMPLATEPATH',
			'STYLESHEETPATH',
			'ADMIN_COOKIE_PATH',
			'WPINC',
			'WP_LANG_DIR',
			'BLOGUPLOADDIR',
			'UPLOADBLOGSDIR',
			'UPLOADS',
		);
	}

	public function get_concerned_options() {
		return array(
			'siteurl',
			'home',
		);
	}

}

add_filter( 'qm/collectors', static function ( array $collectors ) : array {
	$collectors['paths'] = new QMX_Collector_Paths;
	return $collectors;
} );