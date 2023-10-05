<?php declare(strict_types=1);

namespace QMX\Output\Html;

defined( 'WPINC' ) || die();

/**
 * @property-read \QMX\Collector\ACF $collector
 */
class ACF extends \QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );

		add_filter( 'qm/output/panel_menus', array( $this, 'panel_menu' ), 60 );
	}

	public function name() {
		return __( 'Advanced Custom Fields', 'query-monitor-extend' );
	}

	protected static function identify_duplicates() : bool {
		$bool = null;

		if ( ! is_null( $bool ) ) {
			return $bool;
		}

		$bool = (bool) apply_filters( 'qmx/output/acf/identify_duplicates', false );

		return $bool;
	}

	public function output() : void {
		/** @var \QMX\Data\ACF */
		$data = $this->collector->get_data();

		$this->output_fields_table();
		$this->output_local_json();
		$this->output_concerns();

		if ( is_admin() ) {
			$this->output_field_groups_table();
		}
	}

	protected function output_fields_table() : void {
		/** @var \QMX\Data\ACF */
		$data = $this->collector->get_data();

		if ( empty( $data->fields ) ) {
			$this->before_non_tabular_output();
			echo '<div class="qm-notice"><p>No Advanced Custom Fields found.</p></div>';
			$this->after_non_tabular_output();
			return;
		}

		echo '<style>.qm-hide-acf-field, .qm-hide-acf-post, .qm-hide-acf-group, .qm-hide-acf-caller { display: none !important; }</style>';

		natsort( $data->field_keys );
		natsort( $data->post_ids );
		natsort( $data->field_groups );
		natsort( $data->callers );

		$this->before_tabular_output();

		echo '<thead>';

		echo '<tr>';

		echo '<th scope="col" class="qm-sorted-asc qm-sortable-column" role="columnheader" aria-sort="ascending">';
		echo $this->build_sorter( '#' );
		echo '</th>';

		echo '<th scope="col" class="qm-filterable-column">';
		echo $this->build_filter(
			'acf-field',
			$data->field_keys,
			__( 'Field', 'query-monitor' ),
			array(
				'prepend' => array(
					'qmx-acf-no-field' => 'Missing',
				),
			)
		);
		echo '</th>';

		echo '<th scope="col" class="qm-filterable-column">';
		echo $this->build_filter( 'acf-post', $data->post_ids, __( 'Post ID', 'query-monitor' ) );
		echo '</th>';

		echo '<th scope="col" class="qm-filterable-column">';
		echo $this->build_filter( 'acf-group', $data->field_groups, __( 'Group', 'query-monitor' ) );
		echo '</th>';

		echo '<th scope="col" class="qm-filterable-column">';
		echo $this->build_filter( 'acf-caller', array_keys( $data->callers ), __( 'Caller', 'query-monitor' ) );
		echo '</th>';

		echo '</tr>';

		echo '</thead>';

		echo '<tbody>';

		foreach ( $data->fields as $row_num => $row ) {
			$row_attr = array(
				'class' => '',
			);

			if ( ! $row['exists'] ) {
				$row_attr['class'] .= ' qm-warn';
			}

			$row_attr['data-qm-acf-field']  = $row['field']['name'] . ' ' . $row['field']['key'];
			$row_attr['data-qm-acf-post']   = $row['post_id'];
			$row_attr['data-qm-acf-caller'] = $row['caller']['function'] . '()';
			$row_attr['data-qm-acf-group']  = 'qmx-acf-no-group';

			if ( empty( $row['field']['key'] ) ) {
				$row_attr['data-qm-acf-field'] .= ' qmx-acf-no-field';
			}

			if ( ! empty( $row['group'] ) ) {
				$row_attr['data-qm-acf-group'] = $row['group']['key'];
			}

			if (
				! empty( $row['duplicate'] )
				&& static::identify_duplicates()
			) {
				$row_attr['class'] .= ' qm-highlight';
			}

			$attr = '';

			foreach ( $row_attr as $a => $v ) {
				$attr .= ' ' . $a . '="' . esc_attr( trim( ( string ) $v ) ) . '"';
			}

			echo '<tr' . $attr . '>';

			# Number
			echo '<th scope="row" class="qm-row-num qm-num">' . esc_html( (string) ( $row_num + 1 ) ) . '</th>';

			# Field name
			echo '<td class="qm-ltr qm-has-toggle qm-nowrap">';
			$this->output_column_field_name( $row );
			echo '</td>';

			# Post ID
			echo '<td class="qm-ltr">' . esc_html( $row['post_id'] ) . '</td>';

			# Field group
			echo '<td class="qm-ltr">';
			$this->output_column_field_group( $row );
			echo '</td>';

			# Caller
			echo '<td class="qm-row-caller qm-ltr qm-has-toggle qm-nowrap">';
			$this->output_column_caller( $row );
			echo '</td>';

			echo '</tr>';
		}

		echo '</tbody>';

		echo '<tfoot>';
		echo '<tr>';
		printf( '<td colspan="5">Total: %d</td>', count( $data->fields ) );
		echo '</tr>';
		echo '</tfoot>';

		echo '</table>';
		echo '</div>';
	}

	protected function output_field_groups_table() : void {
		/** @var \QMX\Data\ACF */
		$data = $this->collector->get_data();
		$id   = 'qm-acf-loaded_field_groups';
		$name = 'Advanced Custom Fields: Loaded Field Groups';

		printf(
			'<div class="qm qm-concerns" id="%1$s" role="tabpanel" aria-labelledby="%1$s-caption" tabindex="-1">',
			esc_attr( $id )
		);

		echo '<table class="qm-sortable">';

		printf(
			'<caption><h2 id="%1$s-caption">%2$s</h2></caption>',
			esc_attr( $id ),
			esc_html( $name )
		);

		echo '<thead>';

		echo '<tr>';

		echo '<th scope="col">';
		echo esc_html__( 'Field Group', 'query-monitor-extend' );
		echo '</th>';

		echo '<th scope="col">';
		echo esc_html__( 'Key', 'query-monitor-extend' );
		echo '</th>';

		echo '<th scope="col">';
		echo esc_html__( 'Rules', 'query-monitor-extend' );
		echo '</th>';

		echo '</tr>';

		echo '</thead>';

		echo '<tbody>';

		$row_num = 1;

		foreach ( $data->loaded_field_groups as $row ) {
			$class = '';

			if ( 1 === ( $row_num % 2 ) ) {
				$class = ' class="qm-odd"';
			}

			echo '<tr' . $class . '>';

			# Field group name
			echo '<td class="qm-ltr qm-nowrap">';
			$this->output_column_field_group_title( $row );
			echo '</td>';

			# Field group key
			echo '<td class="qm-ltr">';
			$this->output_column_field_group_key( $row );
			echo '</td>';

			# Field group rules
			echo '<td class="qm-ltr qm-nowrap qm-has-inner">';
			$this->output_column_field_group_rules( $row );
			echo '</td>';

			echo '</tr>';

			$row_num++;
		}

		echo '</tbody>';

		echo '<tfoot>';
		echo '<tr>';
		printf( '<td colspan="3">Total: %d</td>', count( $data->loaded_field_groups ) );
		echo '</tr>';
		echo '</tfoot>';

		echo '</table>';
		echo '</div>';
	}

	/**
	 * @param array<string, mixed> $row
	 */
	protected function output_column_field_name( array $row ) : void {
		echo esc_html( $row['field']['name'] );

		if ( ! $row['exists'] ) {
			echo ' (Missing)';
			return;
		}

		if (
			! empty( $row['duplicate'] )
			&& static::identify_duplicates()
		) {
			echo ' (Duplicate)';
		}

		$parent = $row['field']['parent'];

		if ( ! empty( $row['group'] ) ) {
			$parent = $row['group']['key'];
		}

		echo self::build_toggler();

		echo '<div class="qm-toggled qm-supplemental qm-info">';
		echo esc_html( 'Key: ' . $row['field']['key'] );
		echo '<br />' . esc_html( 'Parent: ' . $parent );
		echo '</div>';
	}

	/**
	 * @param array<string, mixed> $row
	 */
	protected function output_column_field_group_title( array $row ) : void {
		$title = $row['title'];

		if ( empty( $title ) ) {
			return;
		}

		if ( empty( $row['id'] ) || ! current_user_can( 'edit_post', $row['id'] ) ) {
			echo esc_html( $title );
			return;
		}

		$url = add_query_arg( array(
			'post'   => $row['id'],
			'action' => 'edit',
		), admin_url( 'post.php' ) );

		printf(
			'<a href="%1$s" class="qm-edit-link">%2$s%3$s</a>',
			esc_url( $url ),
			esc_html( $title ),
			QueryMonitor::icon( 'edit' )
		);
	}

	/**
	 * @param array<string, mixed> $row
	 */
	protected function output_column_field_group_key( array $row ) : void {
		/** @var \QMX\Data\ACF */
		$data  = $this->collector->get_data();
		$group = $row['group'];

		if ( empty( $group ) ) {
			return;
		}

		if (
			! function_exists( 'acf_is_local_field_group' )
			|| ! acf_is_local_field_group( $group )
			|| ! array_key_exists( $group, $data->local_json['groups'] )
		) {
			echo esc_html( $group );
			return;
		}

		$filepath = $data->local_json['groups'][ $group ]['local_file'];

		if ( ! file_exists( $filepath ) ) {
			echo esc_html( $group );
			return;
		}

		echo QM_Output_Html::output_filename( $group, $filepath );
	}

	/**
	 * @param array<string, mixed> $row
	 */
	protected function output_column_field_group( array $row ) : void {
		$group = $row['group'];

		if ( empty( $group ) ) {
			return;
		}

		if ( empty( $group['ID'] ) || ! current_user_can( 'edit_post', $group['ID'] ) ) {
			echo esc_html( $group['title'] );
			return;
		}

		$url = add_query_arg( array(
			'post'   => $group['ID'],
			'action' => 'edit',
		), admin_url( 'post.php' ) );

		printf(
			'<a href="%1$s" class="qm-edit-link">%2$s%3$s</a>',
			esc_url( $url ),
			esc_html( $group['title'] ),
			QueryMonitor::icon( 'edit' )
		);
	}

	/**
	 * @param array<string, mixed> $row
	 */
	protected function output_column_field_group_rules( array $row ) : void {
		$rules = $row['rules'];

		if ( empty( $rules ) ) {
			return;
		}

		echo '<pre>';
		print_r( $rules );
		echo '</pre>';
	}

	/**
	 * @param array<string, mixed> $row
	 */
	protected function output_column_caller( array $row ) : void {
		$trace          = $row['trace'];
		$filtered_trace = $trace->get_display_trace();
		$caller_name    = self::output_filename( $row['caller']['function'] . '()', $row['caller']['file'], $row['caller']['line'] );
		$stack          = array();

		array_shift( $filtered_trace );

		foreach ( $filtered_trace as $item ) {
			$item = wp_parse_args( $item, array(
				'file' => '',
				'line' => '',
			) );

			if ( empty( $item['display'] ) ) {
				continue;
			}

			$stack[] = self::output_filename( $item['display'], $item['file'], $item['line'] );
		}

		if ( 1 < count( $stack ) ) {
			echo self::build_toggler();
		}

		echo '<ol>';
		printf( '<li>%s</li>', $caller_name );

		if ( 1 < count( $stack ) ) {
			echo '<div class="qm-toggled"><li>' . implode( '</li><li>', $stack ) . '</li></div>';
		}

		echo '</ol>';
	}

	protected function output_local_json() : void {
		$id   = 'qm-acf-local_json';
		$name = 'Advanced Custom Fields: Local JSON';
		$data = $this->collector->get_data();

		printf(
			'<div class="qm qm-concerns" id="%1$s" role="tabpanel" aria-labelledby="%1$s-caption" tabindex="-1">',
			esc_attr( $id )
		);

		echo '<table class="qm-sortable">';

		printf(
			'<caption><h2 id="%1$s-caption">%2$s<br />%3$s</h2></caption>',
			esc_attr( $id ),
			esc_html( $name ),
			'<a href="https://www.advancedcustomfields.com/resources/local-json/" rel="noopener noreferrer" class="qm-external-link">Documentation</a>'
		);

		echo '<thead>';
		echo '<tr>';
		echo '<th scope="col">Action</th>';
		echo '<th scope="col">Hook</th>';
		echo '<th scope="col" colspan="2">Paths</th>';
		echo '</tr>';
		echo '</thead>';

		echo '<tbody>';

		if ( ! empty( $data->local_json['save'] ) ) {
			$directory = apply_filters( 'acf/settings/save_json', $data->local_json['save'] );
			echo '<tr>';
			echo '<th scope="row">Save</th>';
			echo '<td><code>acf/settings/save_json</code></td>';
			echo '<td colspan="2">' . QM_Output_Html::output_filename( $this->remove_abspath( $directory ), $directory ) . '</td>';
			echo '</tr>';
		}

		if ( ! empty( $data->local_json['load'] ) ) {
			$i = 0;

			foreach ( $data->local_json['load'] as $path ) {
				echo '<tr>';

				if ( 0 === $i ) {
					echo '<th scope="row" rowspan="' . esc_attr( (string) count( $data->local_json['load'] ) ) . '">Load</th>';
					echo '<td rowspan="' . esc_attr( (string) count( $data->local_json['load'] ) ) . '"><code>acf/settings/load_json</code></td>';
				}

				echo '<td class="qm-num">' . esc_html( (string) $i ) . '</td>';
				echo '<td>' . QM_Output_Html::output_filename( $this->remove_abspath( $path ), $path ) . '</td>';

				echo '</tr>';

				$i++;
			}
		}

		echo '</tbody>';
		echo '</table>';

		$this->output_local_json_field_groups();

		echo '</div>';
	}

	protected function output_local_json_field_groups() : void {
		$data = $this->collector->get_data();

		if ( empty( $data->local_json['groups'] ) ) {
			echo '<section class="qm-non-tabular"><div class="qm-notice"><p>No local JSON field groups found.</p></div></section>';
			return;
		}

		$groups = array();

		foreach ( $data->local_json['groups'] as $group ) {
			$groups[ $group['title'] ] = $group;
		}

		ksort( $groups, SORT_NATURAL );

		echo '<table>';
		echo '<caption><h2>Field Groups</caption>';
		echo '<thead>';
		echo '<tr>';
		echo '<th scope="col">Title</th>';
		echo '<th scope="col">Key</th>';
		echo '<th scope="col">File</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $groups as $group ) {
			printf(
				'<tr><th scope="row">%s</th><td>%s</td><td>%s</td></tr>',
				esc_html( $group['title'] ),
				esc_html( $group['key'] ),
				QM_Output_Html::output_filename( $this->remove_abspath( $group['local_file'] ), $group['local_file'] )
			);
		}

		echo '</tbody>';
		printf( '<tfoot><tr><td colspan="3">Total: <span class="qm-items-number">%d</span></td></tr></tfoot>', count( $data->local_json['groups'] ) );
		echo '</table>';
	}

	public function remove_abspath( string $path ) : string {
		return str_replace( ABSPATH, '', $path );
	}

	/**
	 * @param array<string, array<string, mixed>> $menu
	 * @return array<string, array<string, mixed>>
	 */
	public function panel_menu( array $menu ) {
		/** @var \QMX\Data\ACF */
		$data = $this->collector->get_data();

		if ( empty( $data->local_json['groups'] ) ) {
			$data->local_json['groups'] = array();
		}

		$menu['qm-acf'] = $this->menu( array(
			'title' => esc_html__( 'Advanced Custom Fields', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-acf',
		) );

		if ( is_admin() ) {
			$menu['qm-acf']['children']['loaded_field_groups'] = array(
				'title' => esc_html__( 'Field Groups', 'query-monitor-extend' ) . sprintf( ' (%d)', count( $data->loaded_field_groups ) ),
				'href'  => '#qm-acf-loaded_field_groups',
				'id'    => 'query-monitor-extend-acf-loaded_field_groups',
			);
		}

		$menu['qm-acf']['children']['local_json'] = array(
			'title' => esc_html__( 'Local JSON', 'query-monitor-extend' ) . sprintf( ' (%d)', count( $data->local_json['groups'] ) ),
			'href'  => '#qm-acf-local_json',
			'id'    => 'query-monitor-extend-acf-local_json',
		);

		return $menu;
	}
}
