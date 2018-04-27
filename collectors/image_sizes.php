<?php
/**
 * Image sizes collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_Image_Sizes extends QMX_Collector {

	public $id = 'image_sizes';

	public function __construct() {

		$this->data['sizes'] = array(
			'thumbnail' => array(
				'width'  => intval( get_option( 'thumbnail_size_w' ) ),
				'height' => intval( get_option( 'thumbnail_size_h' ) ),
				'source' => 'native',
				  'crop' => true,
				   'num' => 1,
			),
			'medium' => array(
				 'width' => intval( get_option( 'medium_size_w' ) ),
				'height' => intval( get_option( 'medium_size_h' ) ),
				'source' => 'native',
				  'crop' => false,
				   'num' => 2,
			),
			'medium_large' => array(
				 'width' => intval( get_option( 'medium_large_size_w' ) ),
				'height' => intval( get_option( 'medium_large_size_h' ) ),
				'source' => 'native',
				  'crop' => false,
				   'num' => 3,
			),
			'large' => array(
				 'width' => intval( get_option( 'large_size_w' ) ),
				'height' => intval( get_option( 'large_size_h' ) ),
				'source' => 'native',
				  'crop' => false,
				   'num' => 4,
			),
		);

		add_action( 'plugins_loaded',    array( &$this, 'action__plugins_loaded'    ) );
		add_action( 'after_setup_theme', array( &$this, 'action__after_setup_theme' ) );

	}

	function action__plugins_loaded() {
		if ( 'plugins_loaded' !== current_action() )
			return;

		$this->_process_added_image_sizes( 'plugin' );
	}

	function action__after_setup_theme() {
		if ( 'after_setup_theme' !== current_action() )
			return;

		$this->_process_added_image_sizes( 'theme' );
	}

	protected function _process_added_image_sizes( $source = 'unknown' ) {
		global $_wp_additional_image_sizes;

		$num = count( $this->data['sizes'] );

		if (
			 is_array( $_wp_additional_image_sizes )
			&& !empty( $_wp_additional_image_sizes )
		)
			foreach ( $_wp_additional_image_sizes as $id => $size )
				if ( !array_key_exists( $id, $this->data['sizes'] ) )
					$this->data['sizes'][$id] = array_merge(
						array(
							'num' => ++$num,
							'source' => apply_filters( 'qmx/image-size/source', $source, $id, $size )
						),
						$size
					);
	}

	public function name() {
		return __( 'Image Sizes', 'query-monitor-extend' );
	}

	public function process() {
		$this->_process_added_image_sizes();

		$this->data['sizes'] = array_map( array( &$this, 'add_ratio' ), $this->data['sizes'] );

		$counts = array( 'dimensions' => array(), 'ratios' => array() );

		foreach ( $this->data['sizes'] as $size ) {
			$key = $size['width'] . ':' . $size['height'] . ' - ' . $size['crop'];
			array_key_exists( $key, $counts['dimensions'] )
				? $counts['dimensions'][$key]++
				: $counts['dimensions'][$key] = 1;

			$key = $size['ratio'] . ' - ' . $size['crop'];
			if ( 0 !== $size['ratio'] )
				array_key_exists( $key, $counts['ratios'] )
					? $counts['ratios'][$key]++
					: $counts['ratios'][$key] = 1;
		}

		foreach ( array( 'dimensions', 'ratios' ) as $type )
			$counts[$type] = array_filter( $counts[$type], function( $v ) { return $v > 1; } );

		$this->data['_duplicates'] = $counts;

	}

	private function add_ratio( array $size ) {
		if (
			   !array_key_exists( 'width',  $size )
			|| !array_key_exists( 'height', $size )
		)
			return $size;

		$num1 = $size['width'];
		$num2 = $size['height'];

		while ( 0 !== $num2 ) {
			$t = $num1 % $num2;
			$num1 = $num2;
			$num2 = $t;
		}

		$size['_gcd'] = $num1; // greatest common denominator
		unset( $num1, $num2 );

		$size['ratio'] = (
			0 === $size['height']
			? 0
			: $size['width'] / $size['height']
		);

		return $size;
	}

}

QMX_Collectors::add( new QMX_Collector_Image_Sizes );

?>