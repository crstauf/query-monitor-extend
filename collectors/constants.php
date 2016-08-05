<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Collector_Constants extends QM_Collector {

    public $id = 'qmx-constants';

    public function name() {
        return __( 'Constants', 'query-monitor' );
    }

    public function __construct() {

        global $wpdb;

        parent::__construct();

    }

    public function process() {

        $constants = array_unique(apply_filters('qmx/collect/constants',array(
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
            'DONOTCACHEPAGE',
            'DONOTCACHEDB',
            'DONOTMINIFY',
            'DONOTCDN',
            'DONOTCACHCEOBJECT',
            'QM_HIDE_SELF',
            'QM_SHOW_ALL_HOOKS',
            'QM_HIDE_CORE_HOOKS',
            'QMX_HIDE_INCLUDED_CORE_FILES',
            'QMX_HIDE_INCLUDED_SELF_FILES',
            'QMX_HIGHLIGHT_SUPPRESSEDS',
        )));

        $this->data['constants'] = $constants;

    }

}

function register_qmx_collector_constants( array $collectors, QueryMonitor $qm ) {
	$collectors['qmx-constants'] = new QMX_Collector_Constants;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qmx_collector_constants', 10, 2 );

?>
