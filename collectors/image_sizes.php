<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class CSSLLC_QMX_Collector_ImageSizes extends QM_Collector {

    public $id = 'imagesizes';

    public function name() {
        return __( 'Image Sizes', 'query-monitor' );
    }

    public function __construct() {

        global $wpdb;

        parent::__construct();

    }

    public function process() {
        global $_wp_additional_image_sizes;

        $this->data['imagesizes'] = array_merge(array(
            'thumbnail' => array(
                'width' => intval(get_option('thumbnail_size_w')),
                'height' => intval(get_option('thumbnail_size_h')),
                '_builtin' => true,
                'crop' => true,
            ),
            'medium' => array(
                'width' => intval(get_option('medium_size_w')),
                'height' => intval(get_option('medium_size_h')),
                '_builtin' => true,
                'crop' => false,
            ),
            'medium_large' => array(
                'width' => intval(get_option('medium_large_size_w')),
                'height' => intval(get_option('medium_large_size_h')),
                '_builtin' => true,
                'crop' => false,
            ),
            'large' => array(
                'width' => intval(get_option('large_size_w')),
                'height' => intval(get_option('large_size_h')),
                '_builtin' => true,
                'crop' => false,
            ),
        ),$_wp_additional_image_sizes);

        foreach ($this->data['imagesizes'] as $size => $details) {
            $gcd          = self::gcd($details['width'],$details['height']);
            $width_ratio  = $details['width'] / $gcd;
            $height_ratio = $details['height'] / $gcd;
            $this->data['imagesizes'][$size]['ratio'] = $width_ratio . ':' . $height_ratio;
        }

        $this->data['imagesizes'] = apply_filters('qmx/collect/imagesizes',$this->data['imagesizes']);

    }

    protected static function gcd($num1,$num2) {
        while (0 !== $num2) {
               $t = $num1 % $num2;
            $num1 = $num2;
            $num2 = $t;
        }

        return $num1;
    }

}

function register_cssllc_qmx_collector_imagesizes( array $collectors, QueryMonitor $qm ) {
	$collectors['imagesizes'] = new CSSLLC_QMX_Collector_ImageSizes;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_cssllc_qmx_collector_imagesizes', 10, 2 );

?>
