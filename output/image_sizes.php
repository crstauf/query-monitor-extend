<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class CSSLLC_QMX_Output_Html_ImageSizes extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
	}

	public function output() {

		$data = $this->collector->get_data();

		ksort($data['imagesizes']);

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-third">';

			echo '<table cellspacing="0" class="qm-sortable">' .
				'<thead>' .
					'<tr>' .
						'<th colspan="4">Registered Image Sizes</th>' .
					'</tr>' .
					'<tr>' .
						'<th class="qm-sorted-asc">Name' .
							str_replace(
								'class="qm-sort-controls"',
								'class="qm-sort-controls" style="text-align: left !important;"',
								$this->build_sorter()
							) . '</th>' .
						'<th class="qm-num qm-imagesize-width" style="width: 50px;">Width' . $this->build_sorter() . '</th>' .
						'<th class="qm-num qm-imagesize-height" style="width: 50px;">Height' . $this->build_sorter() . '</th>' .
						'<th style="width: 65px;">' .
							'Built-in ' .
							'<select id="qm-filter-imagesizes-builtin" class="qm-filter" data-filter="imagesize" data-highlight="">' .
								'<option value="">All</option>' .
								'<option value="builtin">Built-in</option>' .
								'<option value="additional">Additional</option>' .
							'</select>' .
						'</th>' .
					'</tr>' .
				'</thead>' .
				'<tfoot>' .
					'<tr>' .
						'<td colspan="4" style="text-align: right !important;">Count: ' . count($data['imagesizes']) . '</td>' .
					'</tr>' .
				'</tfoot>' .
				'<tbody>';

					foreach ($data['imagesizes'] as $name => $details) {
						$is_builtin = array_key_exists('_builtin',$details) && true === $details['_builtin'];
						$is_crop = true === $details['crop'];

						echo '<tr id="qm-imagesize-' . esc_attr($name) . '" class="' . ($is_builtin ? 'qm-imagesizes-builtin' : '') . ($is_crop ? ' qm-imagesize-crop' : '') . '" data-qm-imagesize="' . ($is_builtin ? 'builtin' : 'additional') . '">' .
							'<td class="qm-ltr">' .
								esc_html($name) .
							'</td>' .
							'<td class="qm-num qm-imagesize-width' . (!$is_crop ? ' qm-info' : '') . '">' .
								esc_html($details['width']) .
							'</td>' .
							'<td class="qm-num qm-imagesize-height' . (!$is_crop ? ' qm-info' : '') . '">' .
								esc_html($details['height']) .
							'</td>' .
							'<td class="qm-ltr' . ($is_builtin ? ' qm-true' : '') . '" style="text-align: center !important;">' .
								($is_builtin ? '&#10003;' : '') .
							'</td>' .
						'</tr>';
					}

				echo '</tbody>' .
            '</table>' .
			'<style type="text/css">.qm-hide-imagesize { display: none !important; }</style>' .
        '</div>';

	}

}

function register_cssllc_qmx_output_html_imagesizes( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'imagesizes' ) )
		$output['imagesizes'] = new CSSLLC_QMX_Output_Html_ImageSizes( $collector );
	return $output;
}

add_filter( 'qm/outputter/html', 'register_cssllc_qmx_output_html_imagesizes', 131, 2 );

?>
