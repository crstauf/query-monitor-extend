<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class CSSLLC_QMX_Collector_Multisite extends QM_Collector {

    public $id = 'multisite';

    public function name() {
        return __( 'Multisite Constants', 'query-monitor' );
    }

    public function __construct() {

        global $wpdb;

        parent::__construct();

    }

    public function process() {

        $this->data['multisite'] = apply_filters('qmx/collect/constants/multisite',array(
            'ALLOW_SUBDIRECTORY_INSTALL',
            'BLOGUPLOADDIR',
            'BLOG_ID_CURRENT_SITE',
            'DOMAIN_CURRENT_SITE',
            'DIEONDBERROR',
            'ERRORLOGFILE',
            'MULTISITE',
            'NOBLOGREDIRECT',
            'PATH_CURRENT_SITE',
            'UPLOADBLOGSDIR',
            'SITE_ID_CURRENT_SITE',
            'SUBDOMAIN_INSTALL',
            'SUNRISE',
            'UPLOADS',
            'WPMU_ACCEL_REDIRECT',
            'WPMU_SENDFILE',
            'WP_ALLOW_MULTISITE',
        ));

    }

}

function register_cssllc_qmx_collector_multisite( array $collectors, QueryMonitor $qm ) {
    if ( is_multisite() )
	   $collectors['multisite'] = new CSSLLC_QMX_Collector_Multisite;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_cssllc_qmx_collector_multisite', 10, 2 );

?>
