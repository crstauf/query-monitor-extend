<?php
/**
 * ACF collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_ACF extends QMX_Collector {

	public $id = 'acf';

	protected $data = array(
		'fields' => array(),
	);

	function __construct() {
		parent::__construct();
		add_filter( 'acf/pre_load_value', array( $this, 'filter__acf_pre_load_value' ), 10, 3 );
	}

	public function process() {

		// $this->data['local_json'] = array(
		// 	'save' => $this->remove_abspath( ( string ) apply_filters( 'acf/settings/save_json', array() ) ),
		// 	'load' => apply_filters( 'acf/settings/load_json', ( array ) acf_get_setting( 'load_json' ) ),
		// );
		//
		// $this->data['local_json']['load'] = array_map( array( $this, 'remove_abspath' ), $this->data['local_json']['load'] );
		//
		// $this->data['field_groups'] = acf_get_field_groups();

	}

	function filter__acf_pre_load_value( $short_circuit, $post_id, $field ) {
		$trace = new QM_Backtrace( array( 'ignore_current_filter' => true ) );

		$field = array(
			'field'   => $field,
			'post_id' => $post_id,
			'trace'   => $trace,
			// 'caller'  => $trace->get_caller()['display']
		);

		$this->data['fields'][] = $field;

		return $short_circuit;
	}

	public function get_concerned_actions() {
		$actions = array(
			'acf/init',
		);

		if ( is_admin() ) {
			$actions = array_merge( $actions, array(
				'acf/field_group/admin_enqueue_scripts',
				'acf/field_group/admin_head',
				'acf/field_group/admin_footer',
				'acf/input/admin_enqueue_scripts',
				'acf/input/admin_head',
				'acf/input/admin_footer',
				'acf/input/form_data',
				'acf/render_field',
				'acf/save_post',
				'acf/validate_save_post',
			) );
		}

		sort( $actions, SORT_STRING );

		return $actions;
	}

	public function get_concerned_filters() {
		$filters = array(
			'acf/compatibility',
			'acf/fields/google_map/api',
			'acf/is_field_key',
			'acf/pre_load_value',
			'acf/load_value',
			'acf/format_value',
			'acf/settings',
		);

		if ( is_admin() ) {
			$filters = array_merge( $filters, array(
				'acf/fields/flexible_content/layout_title',
				'acf/fields/post_object/query',
				'acf/fields/post_object/result',
				'acf/fields/relationship/query',
				'acf/fields/relationship/result',
				'acf/fields/taxonomy/query',
				'acf/fields/taxonomy/result',
				'acf/fields/taxonomy/wp_list_categories',
				'acf/prepare_field',
				'acf/register_block_type_args',
				'acf/update_field',
				'acf/update_value',
				'acf/upload_prefilter',
				'acf/validate_attachment',
				'acf/validate_value',
				'acf/pre_save_post',
			) );
		}

		sort( $filters, SORT_STRING );

		return $filters;
	}

	public function get_concerned_constants() {
		return array(
			'ACF_LITE',
		);
	}

	public function remove_abspath( string $path ) : string {
		return str_replace( ABSPATH, '', $path );
	}

}

QMX_Collectors::add( new QMX_Collector_ACF );