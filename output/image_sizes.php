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

        $data['imagesizes'] = apply_filters('qmx/collect/before_output/imagesizes',$data['imagesizes']);

		ksort($data['imagesizes']);

        $origins = array();
        foreach ($data['imagesizes'] as $size => $sizes)
            foreach ($sizes as $size)
                $origins[$size['origin']] = !array_key_exists($size['origin'],$origins)
                    ? 0
                    : ($origins[$size['origin']] + 1);

		echo '<div id="' . esc_attr( $this->collector->id() ) . '" class="qm qm-clear qm-half">';

			echo '<table cellspacing="0" class="qm-sortable">' .
				'<thead>' .
					'<tr>' .
						'<th colspan="6">Registered Image Sizes</th>' .
					'</tr>' .
					'<tr>' .
                        '<th class="qm-num"><br />' . $this->build_sorter() . '</th>' .
						'<th class="qm-sorted-asc">Name' .
							str_replace(
								'class="qm-sort-controls"',
								'class="qm-sort-controls" style="text-align: left !important;"',
								$this->build_sorter()
							) . '</th>' .
						'<th class="qm-num qm-imagesize-width">Width' . $this->build_sorter() . '</th>' .
						'<th class="qm-num qm-imagesize-height">Height' . $this->build_sorter() . '</th>' .
                        '<th class="qm-num qm-imagesize-ratio">Ratio' . $this->build_sorter() . '</th>' .
						'<th style="width: 65px;">' .
							'<span style="white-space: nowrap;">Origin</span>' .
                            $this->build_filter('imagesize-origin',array_keys($origins),'subject') .
						'</th>' .
					'</tr>' .
				'</thead>' .
                '<tbody>';

                    $count = 0;

					foreach ($data['imagesizes'] as $name => $sizes) {
                        $origins = array();
                        foreach ($sizes as $size)
                            $origins[$size['origin']] = 1;
                        foreach ($sizes as $i => $size) {
                            $count++;
                            echo '<tr ' .
                                'data-id="qmx-imagesize-' . esc_attr($name) . '"' .
                                'data-qm-imagesize-origin="' . esc_attr(implode(' ',array_keys($origins))) . '" ' .
                                'data-qm-subject="' . esc_attr($size['origin']) . '"' .
                            '>' .
                                '<td class="qm-num">' . $size['num'] . '</td>' .
                                (1 === count($sizes) || 0 === $i
                                    ? '<td class="qm-ltr qm-imagesize-name"' .
                                        (1 !== count($sizes)
                                            ? ' rowspan="' . count($sizes) . '"'
                                            : '') .
                                        '>' . esc_html($name) . '</td>'
                                    : '<td class="qm-ltr qm-imagesize-name qm-hide-rowspan">' . esc_html($name) . '</td>'
                                ) .
                                self::output_size_details($size) .
                            '</tr>';
                        }
					}

				echo '</tbody>' .
				'<tfoot>' .
                    '<tr class="qm-items-highlighted qm-hide"><td colspan="6">Image Sizes highlighted: <span class="qm-items-number">0</span></td></tr>' .
					'<tr><td colspan="6">Total Image Sizes: ' . $count . '</td></tr>' .
				'</tfoot>' .

            '</table>';

            self::output_styling_scripting();

        echo '</div>';

	}

    protected static function output_size_details($details) {
        $num1 = $details['width'];
        $num2 = $details['height'];

        while (0 !== $num2) {
               $t = $num1 % $num2;
            $num1 = $num2;
            $num2 = $t;
        }

        $gcd = $num1; // greatest common denominator
        unset($num1,$num2);

        $ratio_number = 0 === $details['height'] ? 0 : $details['width'] / $details['height'];

        return
            '<td class="qm-num qm-imagesize-width' . (false === $details['crop'] ? ' qm-info' : '') . '">' .
                esc_html($details['width']) .
            '</td>' .
            '<td class="qm-num qm-imagesize-height' . (false === $details['crop'] ? ' qm-info' : '') . '">' .
                esc_html($details['height']) .
            '</td>' .
            '<td class="qm-num qm-imagesize-ratio">' .
                '<span style="display: none;">' . number_format($ratio_number,12,'.','') . '</span>' .
                '<abbr title="' . $ratio_number . '">' .
                    esc_html(($details['width'] / $gcd) . ':' . ($details['height'] / $gcd)) .
                '</abbr>' .
            '</td>' .
            '<td class="qm-ltr">' . esc_html($details['origin']) . '</td>';
    }

    protected static function output_styling_scripting() {
        wp_enqueue_script('jquery');
        ?>
        <style type="text/css">
            .qm tbody > tr > td.qm-imagesize-name[rowspan] { background-color: #FFF !important; }
            .qm .qm-num.qm-imagesize-ratio { width: 80px !important; }
            #qm-imagesizes .qm-hide-rowspan, .qm-hide-imagesize-origin { display: none !important; }
        </style>
        <script type="text/javascript">
            jQuery(function($) {
                $("#qm-imagesizes table.qm-sortable").on('qm-sort-click',function() {
                    $(this).find('td[rowspan]').removeAttr('rowspan');
                    $(this).find('td.qm-hide-rowspan').removeClass('qm-hide-rowspan');
                });
                $("#qm-imagesizes table.qm-sortable").on('qm-sort-tbody',function(ev,tbody) {
                    var rows = $(tbody).find('tr');
                    for (var r = 0; r < rows.length; r++) {
                        var row = $(rows[r]);
                        var id = row.attr('data-id');
                        while (1) {
                            r++;
                            if ($(rows[r]).attr('data-id') === id) {
                                if ('undefined' === typeof row.attr('rowspan'))
                                    row.find('td.qm-imagesize-name').attr('rowspan',2);
                                else
                                    row.find('td.qm-imagesize-name').attr('rowspan',parseInt(row.find('td.qm-imagesize-name').attr('rowspan')) + 1);
                                $(rows[r]).find('td.qm-imagesize-name').addClass('qm-hide-rowspan');
                            } else {
                                r--;
                                break;
                            }
                        }
                    }
                });
            });
        </script>

        <?php
    }

}

function register_cssllc_qmx_output_html_imagesizes( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'imagesizes' ) )
		$output['imagesizes'] = new CSSLLC_QMX_Output_Html_ImageSizes( $collector );
	return $output;
}

?>
