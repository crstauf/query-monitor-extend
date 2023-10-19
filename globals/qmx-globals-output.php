<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @property-read QMX_Collector_Globals $collector
 */
class QMX_Output_Html_Globals extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function output() {
		$data = $this->collector->get_data();

		if ( ! empty( $data->server ) ) {
			$rows = $data->server;

			echo '<div class="qm" id="qm-global-server">';

				echo '<table class="qm-sortable">';
					echo '<caption class="qm-screen-reader-text">$_SERVER</caption>';
					echo '<thead>';
						echo '<tr>';

							echo '<th scope="col" class="qm-num qm-sorted-asc qm-sortable-column">';
								echo $this->build_sorter( __( '', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-sortable-column">';
								echo $this->build_sorter( __( 'Key', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-ltr">';
								echo __( 'Value', 'query-monitor-extend' );
							echo '</th>';

						echo '</tr>';
					echo '</thead>';

					echo '<tbody>';

						$bools = array( true => 'true', false => 'false' );
						$i     = 1;

						foreach ( $rows as $key => $value ) {
							if ( is_string( $value ) ) {
								$value = stripslashes( $value );
							}

							echo '<tr>';
								echo '<td class="qm-num">' . $i++ . '</td>';
								echo '<td class="qm-ltr" data-qm-sort-weight="' . strtolower( esc_attr( $key ) ) . '"><code style="user-select: all;">' . esc_html( $key ) . '</code></td>';
								echo '<td ' . ( is_bool( $value ) ? ' class="qm-' . $bools[ $value ] . '"' : '' ) . '>' . esc_html( (string) QM_Util::display_variable( $value ) ) . '</td>';
							echo '</tr>';
						}

					echo '</tbody>';

				echo '</table>';

			echo '</div>';

		}

		if ( ! empty( $data->get ) ) {
			$rows = $data->get;

			echo '<div class="qm" id="qm-global-get">';

				echo '<table class="qm-sortable">';
					echo '<caption class="qm-screen-reader-text">$_GET</caption>';
					echo '<thead>';
						echo '<tr>';

							echo '<th scope="col" class="qm-num qm-sorted-asc qm-sortable-column">';
								echo $this->build_sorter( __( '', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-sortable-column">';
								echo $this->build_sorter( __( 'Key', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-ltr">';
								echo __( 'Value', 'query-monitor-extend' );
							echo '</th>';

						echo '</tr>';
					echo '</thead>';

					echo '<tbody>';

						$bools = array( true => 'true', false => 'false' );
						$i     = 1;

						foreach ( $rows as $key => $value ) {
							echo '<tr>';
								echo '<td class="qm-num">' . $i++ . '</td>';
								echo '<td class="qm-ltr" data-qm-sort-weight="' . strtolower( esc_attr( $key ) ) . '"><code style="user-select: all;">' . esc_html( $key ) . '</code></td>';
								echo '<td ' . ( is_bool( $value ) ? ' class="qm-' . $bools[ $value ] . '"' : '' ) . '>' . esc_html( (string) QM_Util::display_variable( stripslashes( $value ) ) ) . '</td>';
							echo '</tr>';
						}

					echo '</tbody>';

				echo '</table>';

			echo '</div>';

		}

		if ( ! empty( $data->post ) ) {
			$rows = $data->post;

			echo '<div class="qm" id="qm-global-post">';

				echo '<table class="qm-sortable">';
					echo '<caption class="qm-screen-reader-text">$_POST</caption>';
					echo '<thead>';
						echo '<tr>';

							echo '<th scope="col" class="qm-num qm-sorted-asc qm-sortable-column">';
								echo $this->build_sorter( __( '', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-sortable-column">';
								echo $this->build_sorter( __( 'Key', 'query-monitor-extend' ) );
							echo '</th>';

							echo '<th scope="col" class="qm-ltr">';
								echo __( 'Value', 'query-monitor-extend' );
							echo '</th>';

						echo '</tr>';
					echo '</thead>';

					echo '<tbody>';

						$bools = array( true => 'true', false => 'false' );
						$i     = 1;

						foreach ( $rows as $key => $value ) {
							echo '<tr>';
								echo '<td class="qm-num">' . $i++ . '</td>';
								echo '<td class="qm-ltr" data-qm-sort-weight="' . strtolower( esc_attr( $key ) ) . '"><code style="user-select: all;">' . esc_html( $key ) . '</code></td>';
								echo '<td ' . ( is_bool( $value ) ? ' class="qm-' . $bools[ $value ] . '"' : '' ) . '>' . esc_html( (string) QM_Util::display_variable( sanitize_textarea_field( $value ) ) ) . '</td>';
							echo '</tr>';
						}

					echo '</tbody>';

				echo '</table>';

			echo '</div>';

		}

	}

	/**
	 * @param array<string, array<string, mixed>> $menu
	 * @return array<string, array<string, mixed>>
	 */
	public function panel_menu( array $menu ) {
		$data = $this->collector->get_data();

		if ( ! empty( $data->server ) ) {
			$menu['global-server'] = $this->menu( array(
				'title' => '$_SERVER',
				'id'    => 'query-monitor-extend-global-server',
				'href'  => '#qm-global-server',
			) );
		}

		if ( ! empty( $data->get ) ) {
			$menu['global-get'] = $this->menu( array(
				'title' => '$_GET',
				'id'    => 'query-monitor-extend-global-get',
				'href'  => '#qm-global-get',
			) );
		}

		if ( ! empty( $data->post ) ) {
			$menu['global-post'] = $this->menu( array(
				'title' => '$_POST',
				'id'    => 'query-monitor-extend-global-post',
				'href'  => '#qm-global-post',
			) );
		}

		return $menu;
	}

}

add_filter( 'qm/outputter/html', static function ( array $output ) : array {
	if ( $collector = QM_Collectors::get( 'globals' ) ) {
		$output['globals'] = new QMX_Output_Html_Globals( $collector );
	}

	return $output;
}, 70 );