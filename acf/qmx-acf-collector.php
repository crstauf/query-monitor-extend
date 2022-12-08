<?php
/**
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/acf
 * Description: Query Monitor collector for Advanced Custom Fields.
 * Version: 1.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

add_action( 'plugin_loaded', 'load_qmx_acf_collector' );

function load_qmx_acf_collector( string $file ) {

	if ( 'query-monitor/query-monitor.php' !== plugin_basename( $file ) ) {
		return;
	}

	remove_action( 'plugin_loaded', __FUNCTION__ );

	if ( !class_exists( 'QueryMonitor' ) ) {
		return;
	}

	if ( defined( 'QMX_DISABLE' ) && QMX_DISABLE ) {
		return;
	}

	class QMX_Collector_ACF extends QM_Collector {

		public $id = 'acf';

		protected $data = array(
			'fields' => array(
				'fields'       => array(),
				'field_keys'   => array(),
				'post_ids'     => array(),
				'callers'      => array(),
				'counts'       => array(),
				'field_groups' => array(),
			),
			'local_json'          => array(),
			'loaded_field_groups' => array(
				'hashes'       => array(),
				'field_groups' => array(),
			),
		);

		/**
		 * Functions to ignore in stack traces.
		 *
		 * @return array
		 */
		protected static function ignore_trace_functions() {
			static $functions = null;

			if ( ! is_null( $functions ) ){
				return $functions;
			}

			$functions = apply_filters( 'qmx/collector/acf/ignore_trace_functions', array(
				'get_field',
				'get_field_object',
				'have_rows',
			) );

			return $functions;
		}

		/**
		 * Get field group of specified field.
		 *
		 * @param string $field_parent
		 *
		 * @return string
		 */
		protected static function get_field_field_group( $field_parent ) {
			if  ( is_null( $parent ) ) {
				return null;
			}

			$group = acf_get_field_group( $field_parent );

			if ( false === $group ) {
				$ancestor = acf_get_field( $field_parent );

				return static::get_field_field_group( $ancestor['parent'] );
			}

			return $group;
		}

		/**
		 * Construct.
		 */
		function __construct() {
			parent::__construct();

			add_action( 'qm/cease', array( $this, 'action__qm_cease' ) );

			add_filter( 'acf/settings/load_json', array( $this, 'filter_acf_setting_load_json' ), 9999 );
			add_filter( 'acf/pre_load_value',     array( $this, 'filter__acf_pre_load_value' ) , 10, 3 );
			add_filter( 'acf/load_field_groups',  array( $this, 'filter__acf_load_field_groups' ) );

			$this->data['local_json']['save'] = apply_filters( 'acf/settings/save_json', get_stylesheet_directory() . '/acf-json' );
		}

		/**
		 * Process (empty).
		 */
		public function process() {}

		/**
		 * Action: qm/cease
		 *
		 * Remove filters on Query Monitor cease.
		 */
		public function action__qm_cease() {
			remove_filter( 'acf/settings/load_json', array( $this, 'filter_acf_setting_load_json' ), 9999 );
			remove_filter( 'acf/pre_load_value',     array( $this, 'filter__acf_pre_load_value' ) , 10, 3 );
			remove_filter( 'acf/load_field_groups',  array( $this, 'filter__acf_load_field_groups' ) );
		}

		/**
		 * Filter: acf/settings/load_json
		 *
		 * @param array $paths
		 *
		 * @return array
		 */
		public function filter__acf_settings_load_json( $paths ) {
			$this->data['local_json']['load'] = $paths;

			return $paths;
		}

		/**
		 * Filter: acf/pre_load_value
		 *
		 * Log the fields that were requested.
		 *
		 * @param null $short_circuit
		 * @param int $post_id
		 * @param array $field
		 *
		 * @return null
		 */
		public function filter__acf_pre_load_value( $short_circuit, $post_id, $field ) {
			$full_trace = apply_filters( 'qmx/collector/acf/full_stack_trace', is_admin(), $post_id, $field );
			$trace      = new QM_Backtrace( array( 'ignore_current_filter' => ! $full_stack_trace ) );

			if ( false === $full_trace && defined( 'ACF_PATH' ) ) {
				foreach ( $trace->get_trace() as $frame ) {
					if (
						in_array( $frame['function'], static::ignore_trace_functions() )
						&& false === stripos( $frame['file'], ACF_PATH )
					) {
						break;
					}

					if ( ! empty( $trace->get_trace()[1] ) ) {
						$trace->ignore( 1 );
					}
				}
			}

			$row = array(
				'field'     => $field,
				'post_id'   => acf_get_valid_post_id( $post_id ),
				'trace'     => $trace,
				'exists'    => ! empty( $field['key'] ),
				'caller'    => $trace->get_trace()[0],
				'group'     => null,
				'hash'      => null,
				'duplicate' => false,
			);

			if ( ! empty( $field['key'] ) ) {
				$row['group'] = static::get_field_field_group( $field['parent'] );
			}

			$hash = wp_hash( json_encode( $row ) );
			$row['hash'] = $hash;

			if ( array_key_exists( $hash, $this->data['fields']['counts'] ) ) {
				$this->data['counts'][ $hash ]++;

				if ( apply_filters( 'qmx/collector/acf/hide_duplicates', false ) ) {
					return $short_circuit;
				}

				$row['duplicate'] = true;
			}

			if ( ! empty( $field['key'] ) ) {
				$this->data['fields']['field_keys'][ $field['key' ] ] = $field['name'];
			} else {
				$this->data['fields']['field_keys'][ $field['name'] ] = $field['name'];
			}

			if ( ! empty( $row ) ) {
				$this->data['fields']['field_groups'][ $row['group']['key'] ] = $row['group']['title'];
			}

			$this->data['fields']['post_ids'][ ( string ) $post_id ] = $post_id;
			$this->data['fields']['callers'][ $row['caller']['function'] . '()' ] = 1;
			$this->data['fields']['counts'][ $hash ] = 1;

			$this->data['fields']['fields'][] = $row;

			return $short_circuit;
		}

		/**
		 * Filter: acf/load_field_groups
		 *
		 * Get the field groups that were loaded.
		 *
		 * @param array $field_groups
		 *
		 * @return array
		 */
		public function filter__acf_load_field_groups( $field_groups ) {
			static $processed = array();

			$hash = wp_hash( json_encode( $field_groups ) );

			if ( in_array( $hash, $processed ) ) {
				return;
			}

			$processed[] = $hash;

			foreach ( $field_groups as $field_group ) {
				$hash = wp_hash( json_encode( $field_group ) );

				if ( array_key_exists( $hash, $this->data['loaded_field_groups']['hashes'] ) ) {
					continue;
				}

				$this->data['loaded_field_groups']['field_groups'][ $hash ] = array(
					'id'    => $field_group['ID'],
					'group' => $field_group['key'],
					'title' => $field_group['title'],
					'rules' => $field_group['location'],
				);
			}

			return $field_groups;
		}

		/**
		 * Get concerned actions.
		 *
		 * @return string[]
		 */
		public function get_concerned_actions() {
			return array(
				'acf/init',
			);
		}

		/**
		 * Get concerned filters.
		 *
		 * @return string[]
		 */
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
					'acf/load_field_groups',
				) );
			}

			sort( $filters, SORT_STRING );

			return $filters;
		}

		/**
		 * Get concerned constants.
		 *
		 * @return string[]
		 */
		public function get_concerned_constants() {
			return array(
				'ACF_LITE',
			);
		}

	}

	QM_Collectors::add( new QMX_Collector_ACF );
}
