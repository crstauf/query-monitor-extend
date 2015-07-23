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

	public static $var_dumps = array();

	function __construct() {
		add_filter('qm/collectors',array(__CLASS__,'register_qm_collector_constants'),20,2);
		add_filter('qm/outputter/html',array(__CLASS__,'register_qm_output_html_constants'),115,2);
		add_filter('qm/collectors',array(__CLASS__,'register_qm_collector_multisite'),20,2);
		add_filter('qm/outputter/html',array(__CLASS__,'register_qm_output_html_multisite'),120,2);
		add_filter('qm/collectors',array(__CLASS__,'register_qm_collector_paths'),20,2);
		add_filter('qm/outputter/html',array(__CLASS__,'register_qm_output_html_paths'),130,2);
		add_filter('qm/collectors',array(__CLASS__,'register_qm_collector_var_dumps'),20,2);
		add_filter('qm/outputter/html',array(__CLASS__,'register_qm_output_html_var_dumps'),130,2);
		add_filter('qm/collect/conditionals',array(__CLASS__,'add_conditionals'),9999999);
		add_filter('qm/output/menu_class',array(__CLASS__,'adminbar_menu_bg'),9999999);
	}

	public static function adminbar_menu_bg($classes) {
		if (2 > count($classes)) return $classes;

		$num = 0;
		if ($collector = QM_Collectors::get( 'db_queries' ))
			if ($expensive = count($collector->get_expensive()))
				$num = $num + $expensive;
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
			if (isset($data['errors']))
				foreach ($data['errors'] as $type => $object) {
					if (!isset($$type)) $$type = count($data['errors'][$type]);
					else $$type += count($data['errors'][$type]);
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
		$conds = array_unique($conds);
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

}

class CSS_QM_Collector_Constants extends QM_Collector {

	public $id = 'constants';

	public function name() {
		return __( 'Constants', 'query-monitor' );
	}

	public function __construct() {

		global $wpdb;

		parent::__construct();

	}

	public function process() {

		$constants = array_unique(array(
			'CORE_UPGRADE_SKIP_NEW_BUNDLED',
			'DISABLE_WP_CRON',
			'IMAGE_EDIT_OVERWRITE',
			'MEDIA_TRASH',
			'WP_POST_REVISIONS',
			'APP_REQUEST',
			'COMMENTS_TEMPLATE',
			'DOING_AJAX',
			'DOING_AUTOSAVE',
			'DOING_CRON',
			'IFRAME_REQUEST',
			'IS_PROFILE_PAGE',
			'SHORTINIT',
			'WP_ADMIN',
			'WP_BLOG_ADMIN',
			'WP_IMPORTING',
			'WP_INSTALLING',
			'WP_INSTALLING_NETWORK',
			'WP_LOAD_IMPORTERS',
			'WP_NETWORK_ADMIN',
			'WP_REPAIRING',
			'WP_SETUP_CONFIG',
			'WP_UNINSTALL_PLUGIN',
			'WP_USER_ADMIN',
			'XMLRPC_REQUEST',
			'WP_ALLOW_REPAIR',
			'ENFORCE_GZIP',
			'FTP_SSH',
			'WP_HTTP_BLOCK_EXTERNAL',
			'NO_HEADER_TEXT',
			'WP_USE_THEMES',
			'SAVEQUERIES',
			'SCRIPT_DEBUG',
			'WP_DEBUG',
			'WP_DEBUG_DISPLAY',
			'WP_DEBUG_LOG',
			'ALLOW_UNFILTERED_UPLOADS',
			'CUSTOM_TAGS',
			'DISALLOW_FILE_EDIT',
			'DISALLOW_FILE_MODS',
			'DISALLOW_UNFILTERED_HTML',
			'FORCE_SSL_ADMIN',
			'FORCE_SSL_LOGIN',
			'WP_CACHE',
			'COMPRESS_CSS',
			'COMPRESS_SCRIPTS',
			'CONCATENATE_SCRIPTS',
			'AUTOSAVE_INTERVAL',
			'EMPTY_TRASH_DAYS',
			'WPLANG',
			'WP_DEFAULT_THEME',
			'WP_CRON_LOCK_TIMEOUT',
			'WP_MAIL_INTERVAL',
			'WP_MAX_MEMORY_LIMIT',
			'WP_MEMORY_LIMIT',
			'DB_CHARSET',
			'DB_COLLATE',
			'WP_ACCESSIBLE_HOSTS',
		));

		sort($constants);

		foreach ($constants as $constant)
			if (defined($constant)) {
				if (is_bool(constant($constant))) $this->data['constants'][$constant] = self::format_bool_constant( $constant );
				else $this->data['constants'][$constant] = constant($constant);
			} else
				$this->data['constants'][$constant] = 'undefined';

	}

}

class CSS_QM_Collector_Multisite extends QM_Collector {

	public $id = 'multisite';

	public function name() {
		return __( 'Multisite Constants', 'query-monitor' );
	}

	public function __construct() {

		global $wpdb;

		parent::__construct();

	}

	public function process() {

		$this->data['multisite'] = array(
			'ALLOW_SUBDIRECTORY_INSTALL' => self::format_bool_constant( 'ALLOW_SUBDIRECTORY_INSTALL' ),
			'BLOGUPLOADDIR' => defined('BLOGUPLOADDIR') ? BLOGUPLOADDIR : 'undefined',
			'BLOG_ID_CURRENT_SITE' => defined('BLOG_ID_CURRENT_SITE') ? BLOG_ID_CURRENT_SITE : 'undefined',
			'DOMAIN_CURRENT_SITE' => defined('DOMAIN_CURRENT_SITE') ? DOMAIN_CURRENT_SITE : 'undefined',
			'DIEONDBERROR' => self::format_bool_constant( 'DIEONDBERROR' ),
			'ERRORLOGFILE' => defined('ERRORLOGFILE') ? ERRORLOGFILE : 'undefined',
			'MULTISITE' => self::format_bool_constant( 'MULTISITE' ),
			'NOBLOGREDIRECT' => defined('NOBLOGREDIRECT') ? NOBLOGREDIRECT : 'undefined',
			'PATH_CURRENT_SITE' => defined('PATH_CURRENT_SITE') ? PATH_CURRENT_SITE : 'undefined',
			'UPLOADBLOGSDIR' => defined('UPLOADBLOGSDIR') ? UPLOADBLOGSDIR : 'undefined',
			'SITE_ID_CURRENT_SITE' => defined('SITE_ID_CURRENT_SITE') ? SITE_ID_CURRENT_SITE : 'undefined',
			'SUBDOMAIN_INSTALL' => self::format_bool_constant( 'SUBDOMAIN_INSTALL' ),
			'SUNRISE' => self::format_bool_constant( 'SUNRISE' ),
			'UPLOADS' => defined('UPLOADS') ? UPLOADS : 'undefined',
			'WPMU_ACCEL_REDIRECT' => self::format_bool_constant( 'WPMU_ACCEL_REDIRECT' ),
			'WPMU_SENDFILE' => self::format_bool_constant( 'WPMU_SENDFILE' ),
			'WP_ALLOW_MULTISITE' => self::format_bool_constant( 'WP_ALLOW_MULTISITE' ),
		);

		ksort($this->data['multisite']);

	}

}

class CSS_QM_Collector_Paths extends QM_Collector {

	public $id = 'paths';

	public function name() {
		return __( 'Paths', 'query-monitor' );
	}

	public function __construct() {

		global $wpdb;

		parent::__construct();

	}

	public function process() {

		$this->data['paths'] = array(
			'ABSPATH' => ABSPATH,
			'COOKIEPATH' => COOKIEPATH,
			'SITECOOKIEPATH' => SITECOOKIEPATH,
			'DOMAIN_CURRENT_SITE' => defined('DOMAIN_CURRENT_SITE') ? DOMAIN_CURRENT_SITE : 'undefined',
			'PATH_CURRENT_SITE' => defined('PATH_CURRENT_SITE') ? PATH_CURRENT_SITE : 'undefined',
			'WP_SITEURL' => (defined('WP_SITEURL') ? WP_SITEURL : 'undefined'),
			'site_url()' => site_url(),
			'get_site_url()' => get_site_url(),
			'network_site_url()' => network_site_url(),
			'WP_HOME' => (defined('WP_HOME') ? WP_HOME : 'undefined'),
			'home_url()' => home_url(),
			'get_home_url()' => get_home_url(),
			'network_home_url()' => network_home_url(),
			'get_home_path()' => get_home_path(),
			'&nbsp;' => '',
			'WP_CONTENT_URL' => WP_CONTENT_URL,
			'WP_CONTENT_DIR' => WP_CONTENT_DIR,
			'content_url()' => content_url(),
			'WP_PLUGIN_URL' => WP_PLUGIN_URL,
			'WP_PLUGIN_DIR' => WP_PLUGIN_DIR,
			'PLUGINS_COOKIE_PATH' => PLUGINS_COOKIE_PATH,
			'plugins_url()' => plugins_url(),
			'plugin_dir_url(__FILE__)' => plugin_dir_url(__FILE__),
			'plugin_dir_path(__FILE__)' => plugin_dir_path(__FILE__),
			'plugin_basename(__FILE__)' => plugin_basename(__FILE__),
			'WPMU_PLUGIN_DIR' => WPMU_PLUGIN_DIR,
			'WPMU_PLUGIN_URL' => WPMU_PLUGIN_URL,
			'get_theme_root()' => get_theme_root(),
			'get_theme_roots()' => get_theme_roots(),
			'get_theme_root_uri()' => get_theme_root_uri(),
			'<br />Template (parent)' => '<br />Stylesheet (child, if exists)',
			'get_template_directory()' => get_template_directory(),
			'TEMPLATEPATH' => TEMPLATEPATH,
			'get_template_directory_uri()' => get_template_directory_uri(),
			'get_stylesheet_uri()' => get_stylesheet_uri(),
			'get_stylesheet_directory()' => get_stylesheet_directory(),
			'STYLESHEETPATH' => STYLESHEETPATH,
			'get_stylesheet_directory_uri()' => get_stylesheet_directory_uri(),
			'&nbsp;&nbsp;&nbsp;' => '',
			'admin_url()' => admin_url(),
			'get_admin_url()' => get_admin_url(),
			'network_admin_url()' => network_admin_url(),
			'ADMIN_COOKIE_PATH' => ADMIN_COOKIE_PATH,
			'WPINC' => WPINC,
			'includes_url()' => includes_url(),
			'WP_LANG_DIR' => WP_LANG_DIR,
			'BLOGUPLOADDIR' => (defined('BLOGUPLOADDIR') ? BLOGUPLOADDIR : 'undefined'),
			'UPLOADBLOGSDIR' => defined('UPLOADBLOGSDIR') ? UPLOADBLOGSDIR : 'undefined',
			'UPLOADS' => defined('UPLOADS') ? UPLOADS : 'undefined',
			'wp_upload_dir()' => 'Array(',
		);

		foreach (wp_upload_dir() as $k => $v)
			$this->data['paths']['&nbsp;&nbsp;&nbsp;[' . $k . ']'] = $v;
		$this->data['paths']['&nbsp;&nbsp;&nbsp;[error]'] = (true === $v ? 'true' : 'false');
		$this->data['paths'][')'] = '';

	}

}

class CSS_QM_Collector_VarDumps extends QM_Collector {

	public $id = 'vardumps';

	public function name() {
		return __( 'Var Dumps (' . count(css_qm_extend::$var_dumps) . ')', 'query-monitor' );
	}

	public function __construct() {

		global $wpdb;

		parent::__construct();

	}

	public function process() {

		$this->data['vardumps'] = css_qm_extend::$var_dumps;

	}

}

if (!function_exists('is_custom_post_type')) {
	function is_custom_post_type() {
		global $wp_query;

		if ( ! isset( $wp_query ) || !function_exists( 'get_post_type_object' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1' );
			return false;
		}

		if (!$wp_query->is_singular()) return false;

		$post_obj = $wp_query->get_queried_object();
		$post_type_obj = get_post_type_object($post_obj->post_type);
		return !$post_type_obj->_builtin;
	}
}

if (!function_exists('QM_dump')) {
	function QM_dump($label,$var,$single_table = false) {
		css_qm_extend::$var_dumps[time() . '_' . $label] = array($var,$single_table);
	}
}

?>
