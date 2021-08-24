<?php
/**
 * ACF collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_ACF extends QMX_Collector {

	public $id = 'acf';

	protected $data = array(
		'fields'       => array(),
		'field_keys'   => array(),
		'post_ids'     => array(),
		'callers'      => array(),
		'counts'       => array(),
		'field_groups' => array(),
		'local_json'   => array(),
	);

	function __construct() {
		parent::__construct();

		add_filter( 'acf/settings/load_json', array( $this, 'filter__acf_settings_load_json' ), 99999 );
		add_filter( 'acf/pre_load_value',     array( $this, 'filter__acf_pre_load_value' ), 10, 3 );

		$this->data['local_json']['save'] = apply_filters( 'acf/settings/save_json', get_stylesheet_directory() . '/acf-json' );
	}

	public function process() {}

	public static function get_fields_group( $parent ) {
		if ( is_null( $parent ) )
			return null;

		$group = acf_get_field_group( $parent );

		if ( false === $group ) {
			$field = acf_get_field( $parent );
			return static::get_fields_group( $field['parent'] );
		}

		return $group;
	}

	public function filter__acf_settings_load_json( $paths ) {
		$this->data['local_json']['load'] = $paths;
		return $paths;
	}

	protected static function get_start_trace_functions() {
		$functions = null;

		if ( !is_null( $functions ) )
			return $functions;

		$functions = apply_filters( 'qmx/collector/acf/start_trace_functions', array(
			'get_field',
			'get_field_object',
			'have_rows',
		) );

		return $functions;
	}

	public function filter__acf_pre_load_value( $short_circuit, $post_id, $field ) {
		$full_stack_trace = apply_filters( 'qmx/collector/acf/full_stack_trace', is_admin(), $post_id, $field );
		$trace = new QM_Backtrace( array( 'ignore_current_filter' => !$full_stack_trace ) );

		if ( false === $full_stack_trace ) {
			foreach ( $trace->get_trace() as $frame ) {
				if (
					in_array( $frame['function'], static::get_start_trace_functions() )
					&& false === stripos( $frame['file'], ACF_PATH )
				)
					break;

				if ( !empty( $trace->get_trace()[1] ) )
					$trace->ignore( 1 );

				$caller = $trace->get_trace()[0];
			}
		}

		$row = array(
			'field'     => $field,
			'post_id'   => acf_get_valid_post_id( $post_id ),
			'trace'     => $trace,
			'exists'    => !empty( $field['key'] ),
			'caller'    => $caller,
			'group'     => null,
			'hash'      => null,
			'duplicate' => false,
		);

		if ( !empty( $field['key'] ) )
			$row['group'] = static::get_fields_group( $field['parent'] );

		$hash = md5( json_encode( $row ) );
		$row['hash'] = $hash;

		if ( array_key_exists( $hash, $this->data['counts'] ) ) {
			$this->data['counts'][ $hash ]++;

			if ( apply_filters( 'qmx/collector/acf/hide_duplicates', false ) )
				return $short_circuit;

			$row['duplicate'] = true;
		}

		if ( !empty( $field['key'] ) )
			$this->data['field_keys'][ $field['name'] ] = $field['name'];
		else
			$this->data['field_keys'][ $field['key'] ] = $field['name'];

		if ( !empty( $row['group'] ) )
			$this->data['field_groups'][ $row['group']['key'] ] = $row['group']['title'];

		$this->data['post_ids'][ ( string ) $post_id ] = $post_id;
		$this->data['callers'][ $row['caller']['function'] . '()' ] = 1;
		$this->data['counts'][ $hash ] = 1;

		$this->data['fields'][] = $row;

		return $short_circuit;
	}

	public function get_concerned_actions() {
		$actions = array(
			'acf/init',
		);

		return $actions;
	}

	public function get_concerned_filters() {
		$filters = array(
			'acf/is_field_group_key',
			'acf/is_field_key',
			'acf/load_field_group',
			'acf/pre_load_post_id',
			'acf/validate_post_id',
			'acf/pre_load_value',
			'acf/load_value',
			'acf/settings/load_json',
		);

		if ( is_admin() ) {
			$filters = array_merge( $filters, array(
				'acf/settings/save_json',
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

}

QMX_Collectors::add( new QMX_Collector_ACF );
