<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class QMX_Collector_ImageSizes extends QM_Collector {

    public $id = 'qmx-image_sizes';

    public function name() {
        return __( 'Image Sizes', 'query-monitor' );
    }

    public function __construct() {

        global $wpdb;

        parent::__construct();

    }

    public function process() {
        global $_wp_additional_image_sizes;
        $adtl = $_wp_additional_image_sizes;

        $this->data['imagesizes'] = array(
            'thumbnail' => array(array(
                'width' => intval(get_option('thumbnail_size_w')),
                'height' => intval(get_option('thumbnail_size_h')),
                'origin' => 'native',
                'crop' => true,
                'num' => 1,
            )),
            'medium' => array(array(
                'width' => intval(get_option('medium_size_w')),
                'height' => intval(get_option('medium_size_h')),
                'origin' => 'native',
                'crop' => false,
                'num' => 2,
            )),
            'medium_large' => array(array(
                'width' => intval(get_option('medium_large_size_w')),
                'height' => intval(get_option('medium_large_size_h')),
                'origin' => 'native',
                'crop' => false,
                'num' => 3,
            )),
            'large' => array(array(
                'width' => intval(get_option('large_size_w')),
                'height' => intval(get_option('large_size_h')),
                'origin' => 'native',
                'crop' => false,
                'num' => 4,
            )),
        );

        $num = 5;
        if (is_array($adtl) && count($adtl))
            foreach ($adtl as $size => $details) {
                $details['num'] = $num++;
                $details['origin'] = 'added';
                $this->data['imagesizes'][$size][] = $details;
            }

    }

}

function register_qmx_collector_imagesizes( array $collectors, QueryMonitor $qm ) {
	$collectors['qmx-image_sizes'] = new QMX_Collector_ImageSizes;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qmx_collector_imagesizes', 10, 2 );

?>
