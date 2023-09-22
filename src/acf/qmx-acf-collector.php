<?php
/**
 * Plugin Name: QMX: ACF Collector
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/acf
 * Description: Query Monitor collector for Advanced Custom Fields.
 * Version: 1.0.1
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

defined( 'QMX_DISABLED' ) || define( 'QMX_DISABLED', false );
defined( 'QMX_TESTED_WITH_QM' ) || define( 'QMX_TESTED_WITH_QM', '3.13.0' );

add_action( 'plugin_loaded', 'load_qmx_acf_collector' );

function load_qmx_acf_collector( string $file ) {

	if ( 'query-monitor/query-monitor.php' !== plugin_basename( $file ) ) {
		return;
	}

	remove_action( 'plugin_loaded', __FUNCTION__ );

	if ( ! class_exists( 'QueryMonitor' ) ) {
		return;
	}

	if ( defined( 'QM_DISABLED' ) && constant( 'QM_DISABLED' ) ) {
		return;
	}

	if ( constant( 'QMX_DISABLED' ) ) {
		return;
	}

	class QMX_Collector_ACF extends QM_DataCollector {

		public $id = 'acf';

		function __construct() {
			parent::__construct();

			add_action( 'acf/init', array( $this, 'action__acf_init' ) );

			add_filter( 'acf/settings/load_json', array( $this, 'filter__acf_settings_load_json' ), 99999 );
			add_filter( 'acf/pre_load_value', array( $this, 'filter__acf_pre_load_value' ), 10, 3);
			add_filter( 'acf/load_field_groups', array( $this, 'filter__acf_load_field_groups' ) );

			// Set default value. Filter applied in output.
			$this->data->local_json['save'] = get_stylesheet_directory() . '/acf-json';
		}

		public function get_storage(): QM_Data {
			do_action( 'qmx/load_data/acf' );
			return new QMX_Data_ACF();
		}

		public function process() {}

		public static function get_fields_group( $parent ) {
			if ( is_null( $parent ) ) {
				return null;
			}

			$group = acf_get_field_group( $parent );

			if ( false === $group ) {
				$field = acf_get_field( $parent );
				return static::get_fields_group( $field['parent'] );
			}

			return $group;
		}

		public function action__acf_init() : void {
			$files = acf_get_local_json_files();
			$groups = array();

			foreach ( $files as $group_key => $filepath ) {
				$groups[ $group_key ] = acf_get_field_group( $group_key );
			}

			$this->data->local_json['groups'] = $groups;
		}

		public function filter__acf_settings_load_json( $paths ) {
			if ( did_action( 'qm/cease' ) ) {
				return $paths;
			}

			$this->data->local_json['load'] = $paths;

			return $paths;
		}

		protected static function get_start_trace_functions() {
			$functions = null;

			if ( ! is_null( $functions ) ) {
				return $functions;
			}

			$functions = apply_filters( 'qmx/collector/acf/start_trace_functions', array(
				'get_field',
				'get_field_object',
				'have_rows',
			) );

			return $functions;
		}

		public function filter__acf_pre_load_value( $short_circuit, $post_id, $field ) {
			if ( did_action( 'qm/cease' ) ) {
				return $short_circuit;
			}

			$full_stack_trace = apply_filters( 'qmx/collector/acf/full_stack_trace', is_admin(), $post_id, $field );
			$trace = new QM_Backtrace( array( 'ignore_current_filter' => ! $full_stack_trace ) );

			if ( false === $full_stack_trace ) {
				foreach ($trace->get_trace() as $frame) {
					if (
						in_array( $frame['function'], static::get_start_trace_functions() )
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
				$row['group'] = static::get_fields_group( $field['parent'] );
			}

			$hash = md5( json_encode( $row ) );
			$row['hash'] = $hash;

			if ( array_key_exists( $hash, $this->data->counts ) ) {
				$this->data->counts[ $hash ]++;

				if ( apply_filters( 'qmx/collector/acf/hide_duplicates', false ) ) {
					return $short_circuit;
				}

				$row['duplicate'] = true;
			}

			if ( ! empty( $field['key'] ) ) {
				$this->data->field_keys[ $field['key'] ] = $field['name'];
			} else {
				$this->data->field_keys[ $field['name'] ] = $field['name'];
			}

			if ( ! empty( $row['group'] ) ) {
				$this->data->field_groups[ $row['group']['key'] ] = $row['group']['title'];
			}

			$this->data->post_ids[ ( string ) $post_id ] = $post_id;
			$this->data->callers[ $row['caller']['function'] . '()' ] = 1;
			$this->data->counts[ $hash ] = 1;

			$this->data->fields[] = $row;

			return $short_circuit;
		}

		public function filter__acf_load_field_groups( $field_groups ) {
			static $processed = array();

			if ( empty( $field_groups ) ) {
				return $field_groups;
			}

			$hash = wp_hash( json_encode( $field_groups ) );

			if ( in_array( $hash, $processed ) ) {
				return;
			}

			$processed[] = $hash;

			foreach ( $field_groups as $field_group ) {
				$key = wp_hash( json_encode( $field_group ) );

				if ( array_key_exists( $key, $this->data->loaded_field_groups ) ) {
					continue;
				}

				$this->data->loaded_field_groups[ $key ] = array(
					'id'    => $field_group['ID'],
					'group' => $field_group['key'],
					'title' => $field_group['title'],
					'rules' => $field_group['location'],
				);
			}

			return $field_groups;
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
					'acf/load_field_groups',
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

	QM_Collectors::add( new QMX_Collector_ACF );
}
