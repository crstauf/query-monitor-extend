<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @extends QM_DataCollector<QMX_Data_ACF>
 * @property-read QMX_Data_ACF $data
 */
class QMX_Collector_ACF extends QM_DataCollector {

	public $id = 'acf';

	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', array( $this, 'action__acf_init' ) );

		add_filter( 'acf/settings/load_json', array( $this, 'filter__acf_settings_load_json' ), 99999 );
		add_filter( 'acf/pre_load_value', array( $this, 'filter__acf_pre_load_value' ), 10, 3 );
		add_filter( 'acf/load_field_groups', array( $this, 'filter__acf_load_field_groups' ) );

		// Set default value. Filter applied in output.
		$this->data->local_json['save'] = get_stylesheet_directory() . '/acf-json';
	}

	public function get_storage(): QM_Data {
		require_once 'qmx-acf-data.php';
		return new QMX_Data_ACF();
	}

	public function process() {
	}

	/**
	 * @param mixed $parent
	 * @return mixed
	 */
	public static function get_fields_group( $parent ) {
		if ( is_null( $parent ) ) {
			return null;
		}

		$group = acf_get_field_group( $parent );

		if ( false === $group ) {
			$field = acf_get_field( $parent );

			if ( false === $field ) {
				return $parent;
			}

			return static::get_fields_group( $field['parent'] );
		}

		return $group;
	}

	public function action__acf_init() : void {
		$files  = acf_get_local_json_files();
		$groups = array();

		foreach ( $files as $group_key => $filepath ) {
			$groups[ $group_key ] = acf_get_field_group( $group_key );
		}

		$this->data->local_json['groups'] = $groups;
	}

	/**
	 * @param string[] $paths
	 * @return string[]
	 */
	public function filter__acf_settings_load_json( array $paths ) : array {
		if ( did_action( 'qm/cease' ) ) {
			return $paths;
		}

		$this->data->local_json['load'] = $paths;

		return $paths;
	}

	/**
	 * @return string[]
	 */
	protected static function get_start_trace_functions() {
		$functions = null;

		if ( ! is_null( $functions ) ) {
			return $functions;
		}

		$functions = (array) apply_filters( 'qmx/collector/acf/start_trace_functions', array(
			'get_field',
			'get_field_object',
			'have_rows',
		) );

		$functions = array_filter( $functions, static function ( $item ) {
			return is_string( $item );
		} );

		return $functions;
	}

	/**
	 * @param mixed $short_circuit
	 * @param int|string $post_id
	 * @param array<mixed> $field
	 * @return mixed
	 */
	public function filter__acf_pre_load_value( $short_circuit, $post_id, $field ) {
		if ( did_action( 'qm/cease' ) || ! function_exists( 'acf_get_valid_post_id' ) ) {
			return $short_circuit;
		}

		$args             = array();
		$full_stack_trace = apply_filters( 'qmx/collector/acf/full_stack_trace', is_admin(), $post_id, $field );

		if ( ! $full_stack_trace ) {
			$args['ignore_hook'] = array( current_filter() );
		};

		$trace = new QM_Backtrace( $args );

		if ( false === $full_stack_trace ) {
			foreach ( $trace->get_trace() as $frame ) {
				if (
					in_array( $frame['function'], static::get_start_trace_functions() )
					&& false === stripos( $frame['file'], constant( 'ACF_PATH' ) )
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

		$hash        = md5( (string) json_encode( $row ) );
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
			$key   = $row['group'];
			$title = $key;

			if ( is_array( $row['group'] ) ) {
				$key   = $row['group']['key'];
				$title = $row['group']['title'];
			}
			
			$this->data->field_groups[ $key ] = $title;
		}

		$this->data->post_ids[ ( string ) $post_id ]              = $post_id;
		$this->data->callers[ $row['caller']['function'] . '()' ] = 1;
		$this->data->counts[ $hash ]                              = 1;
		$this->data->fields[]                                     = $row;

		return $short_circuit;
	}

	/**
	 * @param array<mixed> $field_groups
	 * @return array<mixed>
	 */
	public function filter__acf_load_field_groups( array $field_groups ) : array {
		static $processed = array();

		if ( empty( $field_groups ) ) {
			return $field_groups;
		}

		$hash = wp_hash( (string) json_encode( $field_groups ) );

		if ( in_array( $hash, $processed ) ) {
			return $field_groups;
		}

		$processed[] = $hash;

		foreach ( $field_groups as $field_group ) {
			$key = wp_hash( (string) json_encode( $field_group ) );

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
