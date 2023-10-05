<?php declare(strict_types=1);

namespace QMX\Data;

defined( 'WPINC' ) || die();

class ImageSizes extends \QM_Data {

	/**
	 * @var array<string, array<string, mixed>> $sizes
	 */
	public $sizes = array();

	/**
	 * @var array<string, array<string, mixed>>
	 */
	public $duplicates = array();

}
