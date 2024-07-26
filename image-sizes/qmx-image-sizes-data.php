<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

class QMX_Data_Image_Sizes extends QM_Data {

	/**
	 * @var array<string, array<string, mixed>> $sizes
	 */
	public $sizes = array();

	/**
	 * @var array<string, array<string, mixed>>
	 */
	public $duplicates = array();

}
