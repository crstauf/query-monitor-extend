<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * Sub-panel outputter for the Loaded Field Groups view (admin only).
 *
 * @property-read QMX_Collector_ACF $collector
 */
class QMX_Output_Html_ACF_LoadedFieldGroups extends QM_Output_Html {

	public function name() {
		return __( 'Advanced Custom Fields: Loaded Field Groups', 'query-monitor-extend' );
	}

	public function output() : void {
		/** @var QMX_Data_ACF */
		$data = $this->collector->get_data();
		$id   = 'qm-acf-loaded_field_groups';
		$name = $this->name();

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
		echo '<th scope="col">' . esc_html__( 'Field Group', 'query-monitor-extend' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Key', 'query-monitor-extend' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Rules', 'query-monitor-extend' ) . '</th>';
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

			echo '<td class="qm-ltr qm-nowrap">';
			$this->output_column_title( $row );
			echo '</td>';

			echo '<td class="qm-ltr">';
			$this->output_column_key( $row );
			echo '</td>';

			echo '<td class="qm-ltr qm-nowrap qm-has-inner">';
			$this->output_column_rules( $row );
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
	protected function output_column_title( array $row ) : void {
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
	protected function output_column_key( array $row ) : void {
		/** @var QMX_Data_ACF */
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
	protected function output_column_rules( array $row ) : void {
		$rules = $row['rules'];

		if ( empty( $rules ) ) {
			return;
		}

		echo '<pre>';
		print_r( $rules );
		echo '</pre>';
	}
}
