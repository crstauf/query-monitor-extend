<?php
/**
 * Paths output for HTML pages.
 *
 * @package query-monitor-extend
 */

class QMX_Output_Html_Time extends QMX_Output_Html {

	public function __construct( QMX_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function output() {
		$data = $this->collector->get_data();
		$wp_offset = get_option( 'gmt_offset' );

		echo '<div class="qm qm-non-tabular" id="' . esc_attr( $this->collector->id() ) . '">' .
			'<div class="qm-boxed">';

				foreach ( $data['functions'] as $label => $function ) {
					if ( is_callable( array( $this->collector, $function ) ) )
						echo '<div class="qm-section">' .
							'<h2>' . esc_html( $label ) . '</h2>' .
							'<p><code id="qm-time-' . sanitize_title( $label ) . '">' . $this->collector->$function() . '</code></p>' .
						'</div>';
				}

			echo '</div>';
			?>

			<script type="text/javascript">
				( function() {
					if ( 'function' !== typeof IntersectionObserver )
						return;

					var qmx_time_interval = 0;

					var observer = new IntersectionObserver( function( entries, observer ) {
						if ( !entries[0].isIntersecting ) {
							clearInterval( qmx_time_interval );
							return;
						}

						var qmx_time_months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
						var qmx_time_days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

						var qmx_time_container = document.getElementById( 'qm-time' );
						var qmx_time_utc = document.getElementById( 'qm-time-utc' );
						var qmx_time_server = document.getElementById( 'qm-time-server' );
						var qmx_time_wp = document.getElementById( 'qm-time-wordpress' );
						var qmx_time_browser = document.getElementById( 'qm-time-browser' );

						if ( qmx_time_container ) {
							qmx_time_interval = setInterval( function() {
								var d = new Date();
								var UTC_string = d.toUTCString();
								var utc_time = d.getTime() + ( d.getTimezoneOffset() * 60 * 1000 );
								var server = new Date( utc_time + ( <?php echo esc_js( $this->collector->get_server_offset() ) ?> * 1000 ) );
								var wp = new Date( utc_time + ( <?php echo esc_js( $this->collector->get_wp_offset() * HOUR_IN_SECONDS ) ?> * 1000 ) );

								qmx_time_utc.innerHTML =
									qmx_time_days[d.getUTCDay()] + ', '
									+ qmx_time_months[d.getUTCMonth()] + ' '
									+ d.getUTCDate() + ', '
									+ d.getUTCFullYear() + ' '
									+ ( 10 > d.getUTCHours() ? '0' : '' ) + d.getUTCHours()
									+ ':' + ( 10 > d.getUTCMinutes() ? '0' : '' ) + d.getUTCMinutes()
									+ ':' + ( 10 > d.getUTCSeconds() ? '0' : '' ) + d.getUTCSeconds();

								qmx_time_server.innerHTML =
									qmx_time_days[server.getDay()] + ', '
									+ qmx_time_months[server.getMonth()] + ' '
									+ server.getDate() + ', '
									+ server.getFullYear() + ' '
									+ ( 10 > server.getHours() ? '0' : '' ) + server.getHours()
									+ ':' + ( 10 > server.getMinutes() ? '0' : '' ) + server.getMinutes()
									+ ':' + ( 10 > server.getSeconds() ? '0' : '' ) + server.getSeconds();

								qmx_time_wp.innerHTML =
									qmx_time_days[wp.getDay()] + ', '
									+ qmx_time_months[wp.getMonth()] + ' '
									+ wp.getDate() + ', '
									+ wp.getFullYear() + ' '
									+ ( 10 > wp.getHours() ? '0' : '' ) + wp.getHours()
									+ ':' + ( 10 > wp.getMinutes() ? '0' : '' ) + wp.getMinutes()
									+ ':' + ( 10 > wp.getSeconds() ? '0' : '' ) + wp.getSeconds();

								qmx_time_browser.innerHTML =
									qmx_time_days[d.getDay()] + ', '
									+ qmx_time_months[d.getMonth()] + ' '
									+ d.getDate() + ', '
									+ d.getFullYear() + ' '
									+ ( 10 > d.getHours() ? '0' : '' ) + d.getHours()
									+ ':' + ( 10 > d.getMinutes() ? '0' : '' ) + d.getMinutes()
									+ ':' + ( 10 > d.getSeconds() ? '0' : '' ) + d.getSeconds();
							}, 1000 );
						}
					} );

					observer.observe( document.getElementById( 'qm-time' ) );

				} () );
			</script>

			<?php
		echo '</div>';

	}

	public function panel_menu( array $menu ) {

		$menu['time'] = $this->menu( array(
			'title' => esc_html__( 'Time', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-time',
		) );

		return $menu;

	}

}

function register_qmx_output_html_time( array $output ) {
	if ( $collector = QMX_Collectors::get( 'time' ) ) {
		$output['time'] = new QMX_Output_Html_Time( $collector );
	}
	return $output;
}

add_filter( 'qmx/outputter/html', 'register_qmx_output_html_time', 70 );