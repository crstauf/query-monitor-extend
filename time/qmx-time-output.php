<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * @property-read QMX_Collector_Time $collector
 */
class QMX_Output_Html_Time extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/panel_menus', array( &$this, 'panel_menu' ), 60 );
	}

	public function output() {
		$wp_offset = get_option( 'gmt_offset' );
		/** @var QMX_Data_Time $data */
		$data = $this->collector->get_data();

		echo '<div class="qm qm-non-tabular" id="' . esc_attr( $this->collector->id() ) . '">' .
			'<div class="qm-boxed">';

				foreach ( $data->functions as $label => $function ) {
					if ( is_callable( array( $this->collector, $function ) ) ) {
						echo '<div class="qm-section">' .
							'<h2>' . esc_html( $label ) . '</h2>' .
							'<p><code id="qm-time-' . sanitize_title( $label ) . '">' . $this->collector->$function() . '</code></p>' .
						'</div>';
					}
				}

			echo '</div>';
			?>

			<script type="text/javascript">
				(function() {
					if ('function' !== typeof IntersectionObserver) return;

					// Function that contains your original logic
					function initializeQueryMonitorTime() {
						var container = document.querySelector('#query-monitor-container');
						var qmx_time_interval = 0;

						var observer = new IntersectionObserver(function(entries, obs) {
							if (!entries[0].isIntersecting) {
								clearInterval(qmx_time_interval);
								return;
							}

							var qmx_time_months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
							var qmx_time_days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

							var qmx_time_container = container.shadowRoot.getElementById('qm-time');
							var qmx_time_utc       = container.shadowRoot.getElementById('qm-time-utc');
							var qmx_time_server    = container.shadowRoot.getElementById('qm-time-server');
							var qmx_time_wp        = container.shadowRoot.getElementById('qm-time-wordpress');
							var qmx_time_browser   = container.shadowRoot.getElementById('qm-time-browser');

							if (qmx_time_container) {
								qmx_time_interval = setInterval(function() {
									var d = new Date();
									var UTC_string = d.toUTCString();
									var utc_time = d.getTime() + (d.getTimezoneOffset() * 60 * 1000);
									var server = new Date(utc_time + (<?php echo esc_js( $this->collector->get_server_offset() ) ?> * 1000));
									var wp = new Date(utc_time + (<?php echo esc_js( (string) ( $this->collector->get_wp_offset() * HOUR_IN_SECONDS ) ) ?> * 1000 ) );

									// UTC
									qmx_time_utc.innerHTML =
										qmx_time_days[d.getUTCDay()] + ', ' +
										qmx_time_months[d.getUTCMonth()] + ' ' +
										d.getUTCDate() + ', ' +
										d.getUTCFullYear() + ' ' +
										(10 > d.getUTCHours() ? '0' : '') + d.getUTCHours() + ':' +
										(10 > d.getUTCMinutes() ? '0' : '') + d.getUTCMinutes() + ':' +
										(10 > d.getUTCSeconds() ? '0' : '') + d.getUTCSeconds();

									// Server time
									qmx_time_server.innerHTML =
										qmx_time_days[server.getDay()] + ', ' +
										qmx_time_months[server.getMonth()] + ' ' +
										server.getDate() + ', ' +
										server.getFullYear() + ' ' +
										(10 > server.getHours() ? '0' : '') + server.getHours() + ':' +
										(10 > server.getMinutes() ? '0' : '') + server.getMinutes() + ':' +
										(10 > server.getSeconds() ? '0' : '') + server.getSeconds() +
										' <?php echo esc_js( $this->collector->get_server_timezone() ); ?>';

									// WordPress time
									qmx_time_wp.innerHTML =
										qmx_time_days[wp.getDay()] + ', ' +
										qmx_time_months[wp.getMonth()] + ' ' +
										wp.getDate() + ', ' +
										wp.getFullYear() + ' ' +
										(10 > wp.getHours() ? '0' : '') + wp.getHours() + ':' +
										(10 > wp.getMinutes() ? '0' : '') + wp.getMinutes() + ':' +
										(10 > wp.getSeconds() ? '0' : '') + wp.getSeconds() +
										' <?php echo esc_js( $this->collector->get_wp_timezone() ); ?>';

									// Browser time
									qmx_time_browser.innerHTML =
										qmx_time_days[d.getDay()] + ', ' +
										qmx_time_months[d.getMonth()] + ' ' +
										d.getDate() + ', ' +
										d.getFullYear() + ' ' +
										(10 > d.getHours() ? '0' : '') + d.getHours() + ':' +
										(10 > d.getMinutes() ? '0' : '') + d.getMinutes() + ':' +
										(10 > d.getSeconds() ? '0' : '') + d.getSeconds() +
										' ' + getBrowserTimezoneAbbr(d);

								}, 1000);
							}
						});

						// Observe the element inside shadow DOM

						if (container && container.shadowRoot) {
							var target = container.shadowRoot.getElementById('qm-time');
							if (target) {
								observer.observe(target);
							}
						}
					}

					// Wait for #query-monitor-container to be available
					function waitForContainer() {
						var container = document.querySelector('#query-monitor-container');

						if (container && container.shadowRoot) {
							initializeQueryMonitorTime();
							return;
						}

						// Retry every 100ms (adjust if needed)
						var attempts = 0;
						var maxAttempts = 100; // ~10 seconds max

						var interval = setInterval(function() {
							attempts++;
							container = document.querySelector('#query-monitor-container');

							if (container && container.shadowRoot) {
								clearInterval(interval);
								initializeQueryMonitorTime();
							} else if (attempts >= maxAttempts) {
								clearInterval(interval);
								// Optional: console.warn('Query Monitor container not found after timeout');
							}
						}, 100);
					}

					function getBrowserTimezoneAbbr(d) {
						try {
							// Modern & recommended way
							return new Intl.DateTimeFormat('en-US', {
								timeZoneName: 'short'
							}).formatToParts(d)
								.find(part => part.type === 'timeZoneName').value;
						} catch (e) {
							// Fallback for older browsers
							const offset = d.getTimezoneOffset();
							const hours = Math.abs(Math.floor(offset / 60));
							const minutes = Math.abs(offset % 60);

							if (minutes === 0) {
								return (offset > 0 ? 'GMT-' : 'GMT+') + hours;
							} else {
								return (offset > 0 ? 'GMT-' : 'GMT+') +
									hours + ':' + (minutes < 10 ? '0' : '') + minutes;
							}
						}
					}

					// Start waiting
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', waitForContainer);
					} else {
						waitForContainer();
					}

				})();
			</script>

			<?php
		echo '</div>';
	}

	/**
	 * @param array<string, array<string, mixed>> $menu
	 * @return array<string, array<string, mixed>>
	 */
	public function panel_menu( array $menu ) {
		$menu['time'] = $this->menu( array(
			'title' => esc_html__( 'Time', 'query-monitor-extend' ),
			'id'    => 'query-monitor-extend-time',
		) );

		return $menu;
	}

}

add_filter( 'qm/outputter/html', static function ( array $output ) : array {
	if ( $collector = QM_Collectors::get( 'time' ) ) {
		$output['time'] = new QMX_Output_Html_Time( $collector );
	}

	return $output;
}, 70 );
