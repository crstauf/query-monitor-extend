<?php declare(strict_types=1);

namespace QMX\Collector;

defined( 'WPINC' ) || die();

/**
 * @extends \QM_DataCollector<\QMX\Data\ImageSizes>
 * @property-write \QMX\Data\ImageSizes $data
 */
class ImageSizes extends \QM_DataCollector {

	public $id = 'image_sizes';

	public function __construct() {
		parent::__construct();

		$this->data->sizes = array(
			'thumbnail'    => array(
				'width'  => intval( get_option( 'thumbnail_size_w' ) ),
				'height' => intval( get_option( 'thumbnail_size_h' ) ),
				  'used' => 0,
				'source' => 'native',
				  'crop' => true,
				   'num' => 1,
			),
			'medium'       => array(
				 'width' => intval( get_option( 'medium_size_w' ) ),
				'height' => intval( get_option( 'medium_size_h' ) ),
				  'used' => 0,
				'source' => 'native',
				  'crop' => false,
				   'num' => 2,
			),
			'medium_large' => array(
				 'width' => intval( get_option( 'medium_large_size_w' ) ),
				'height' => intval( get_option( 'medium_large_size_h' ) ),
				  'used' => 0,
				'source' => 'native',
				  'crop' => false,
				   'num' => 3,
			),
			'large'        => array(
				 'width' => intval( get_option( 'large_size_w' ) ),
				'height' => intval( get_option( 'large_size_h' ) ),
				  'used' => 0,
				'source' => 'native',
				  'crop' => false,
				   'num' => 4,
			),

			/**
			 * @see _wp_add_additional_image_sizes()
			 * @since WP 5.3.0
			 */
			'1536x1536'    => array(
				 'width' => 1536,
				'height' => 1536,
				  'used' => 0,
				'source' => 'native',
				  'crop' => false,
				   'num' => 5,
			),
			'2048x2048'    => array(
				 'width' => 2048,
				'height' => 2048,
				  'used' => 0,
				'source' => 'native',
				  'crop' => false,
				   'num' => 6,
			),
		);

		add_action( 'plugins_loaded', array( &$this, 'action__plugins_loaded'    ) );
		add_action( 'after_setup_theme', array( &$this, 'action__after_setup_theme' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'add_inline_script' ), -998 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'add_inline_script' ), -998 );
		add_action( 'login_enqueue_scripts', array( &$this, 'add_inline_script' ), -998 );
		add_action( 'enqueue_embed_scripts', array( &$this, 'add_inline_script' ), -998 );

		add_action( 'wp', array( $this, 'action__wp' ) );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'filter__wp_get_attachment_image_src' ), 10, 3 );
	}

	public function get_storage(): \QM_Data {
		require_once 'qmx-image-sizes-data.php';
		return new QMX_Data_Image_Sizes();
	}

	public function get_concerned_filters() {
		return array(
			'wp_get_attachment_image_src',
		);
	}

	public function action__plugins_loaded() : void {
		if ( 'plugins_loaded' !== current_action() || did_action( 'qm/cease' ) ) {
			return;
		}

		$this->process_added_image_sizes( 'plugin' );
	}

	public function action__after_setup_theme() : void {
		if ( 'after_setup_theme' !== current_action() || did_action( 'qm/cease' ) ) {
			return;
		}

		$this->process_added_image_sizes( 'theme' );
	}

	public function action__wp() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$post = get_queried_object();

		if ( empty( $post ) || ! is_object( $post ) || ! is_a( $post, WP_Post::class ) ) {
			return;
		}

		$blocks = parse_blocks( $post->post_content );

		if ( empty( $blocks ) ) {
			return;
		}

		foreach ( $blocks as $block ) {
			if ( 'core/image' !== $block['blockName'] || empty( $block['attrs'] ) || empty( $block['attrs']['sizeSlug'] ) ) {
				continue;
			}

			$size = $block['attrs']['sizeSlug'];

			if ( ! array_key_exists( $size, $this->data->sizes ) ) {
				continue;
			}

			$this->data->sizes[ $size ]['used']++;
		}
	}

	/**
	 * @param false|array<int, string> $image
	 * @param int $attachment_id
	 * @param string|int[] $size
	 * @return false|array<int, string>
	 */
	public function filter__wp_get_attachment_image_src( $image, int $attachment_id, $size ) {
		if ( did_action( 'qm/cease' ) ) {
			return $image;
		}

		# If specifying custom dimensions, bail.
		if ( is_array( $size ) ) {
			return $image;
		}

		# If size is not registered, bail.
		if ( ! array_key_exists( $size, $this->data->sizes ) ) {
			return $image;
		}

		$this->data->sizes[ $size ]['used']++;

		return $image;
	}

	/**
	 * @param string $source
	 * @return void
	 */
	protected function process_added_image_sizes( string $source = 'unknown' ) : void {
		global $_wp_additional_image_sizes;

		$num = count( $this->data->sizes );

		if (
			 is_array( $_wp_additional_image_sizes )
			&& ! empty( $_wp_additional_image_sizes )
		) {
			foreach ( $_wp_additional_image_sizes as $id => $size ) {
				if ( ! array_key_exists( $id, $this->data->sizes ) ) {
					$this->data->sizes[ $id ] = array_merge(
						array(
							'num'    => ++$num,
							'source' => apply_filters( 'qmx/image-size/source', $source, $id, $size ),
							'used'   => 0,
						),
						$size
					);
				}
			}
		}
	}

	public function process() {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		$this->process_added_image_sizes();

		$this->data->sizes = array_map( array( &$this, 'add_ratio' ), $this->data->sizes );

		$counts = array( 'dimensions' => array(), 'ratios' => array() );

		foreach ( $this->data->sizes as $size ) {
			$key = $size['width'] . ':' . $size['height'] . ' - ' . ( bool ) $size['crop'];

			if ( ! array_key_exists( $key, $counts['dimensions'] ) ) {
				$counts['dimensions'][ $key ] = 0;
			}

			$counts['dimensions'][ $key ]++;

			$key = $size['ratio'] . ' - ' . ( bool ) $size['crop'];
			if ( 0 !== $size['ratio'] ) {
				if ( ! array_key_exists( $key, $counts['ratios'] ) ) {
					$counts['ratios'][ $key ] = 0;
				}

				$counts['ratios'][ $key ]++;
			}
		}

		foreach ( array( 'dimensions', 'ratios' ) as $type ) {
			$counts[ $type ] = array_filter( $counts[ $type ], function ( $v ) {
				return $v > 1;
			} );
		}

		$this->data->duplicates = $counts;
	}

	/**
	 * @param array<string, int> $size
	 * @return array<string, int|float>
	 */
	private function add_ratio( array $size ) : array {
		if (
			   ! array_key_exists( 'width', $size )
			|| ! array_key_exists( 'height', $size )
		) {
			return $size;
		}

		$num1 = $size['width'];
		$num2 = $size['height'];

		while ( 0 !== $num2 ) {
			$t    = $num1 % $num2;
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

	public function add_inline_script() : void {
		if ( did_action( 'qm/cease' ) ) {
			return;
		}

		wp_add_inline_script( 'query-monitor', $this->inlineScript_queryMonitor() );
	}

	protected function inlineScript_queryMonitor() : string {
		ob_start();
		?>

		if ( window.jQuery ) {

			jQuery( function( $ ) {

				$( 'td[data-qmx-image-size-width]' )
					.on( 'mouseenter', function() { qmx_image_size_highlighter__mouseenter( 'width', this ); } )
					.on( 'mouseleave', function() { qmx_image_size_highlighter__mouseleave( 'width', this ); } );

				$( 'td[data-qmx-image-size-height]' )
					.on( 'mouseenter', function() { qmx_image_size_highlighter__mouseenter( 'height', this ); } )
					.on( 'mouseleave', function() { qmx_image_size_highlighter__mouseleave( 'height', this ); } );

				$( 'td[data-qmx-image-size-ratio]' )
					.on( 'mouseenter', function() { qmx_image_size_highlighter__mouseenter( 'ratio', this ); } )
					.on( 'mouseleave', function() { qmx_image_size_highlighter__mouseleave( 'ratio', this ); } );

			} );

			function qmx_image_size_highlighter__mouseenter( prop, el ) {
				jQuery( el ).addClass( 'qm-highlight' );
				var tr = jQuery( el ).closest( 'tr' );
				var value = jQuery( el ).attr( 'data-qmx-image-size-' + prop );
				var table = jQuery( el ).closest( 'table' ).find( 'tr[data-qmx-image-size-' + prop + '="' + value + '"]' ).not( tr ).addClass( 'qm-highlight' );
			}

			function qmx_image_size_highlighter__mouseleave( prop, el ) {
				jQuery( el ).removeClass( 'qm-highlight' );
				jQuery( el ).closest( 'table' ).find( 'tr.qm-highlight' ).removeClass( 'qm-highlight' );
			}

		}

		<?php
		return ( string ) ob_get_clean();
	}

}
