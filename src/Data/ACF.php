<?php declare(strict_types=1);

namespace QMX\Data;

defined( 'WPINC' ) || die();

class ACF extends \QM_Data {

	/**
	 * @var array<mixed> $fields
	 */
	public $fields = array();

	/**
	 * @var array<mixed> $field_keys
	 */
	public $field_keys = array();

	/**
	 * @var array<mixed> $post_ids
	 */
	public $post_ids = array();

	/**
	 * @var array<mixed> $callers
	 */
	public $callers = array();

	/**
	 * @var array<mixed> $counts
	 */
	public $counts = array();

	/**
	 * @var array<mixed> $field_groups
	 */
	public $field_groups = array();

	/**
	 * @var array<mixed> $local_json
	 */
	public $local_json = array();

	/**
	 * @var array<mixed> $loaded_field_groups
	 */
	public $loaded_field_groups = array();

}
