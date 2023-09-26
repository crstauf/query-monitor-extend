<?php

defined( 'WPINC' ) || die();

class QMX_Data_ACF extends QM_Data {

	public $fields = array();
	public $field_keys = array();
	public $post_ids = array();
	public $callers = array();
	public $counts = array();
	public $field_groups = array();
	public $local_json = array();
	public $loaded_field_groups = array();

}