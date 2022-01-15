<?php
/**
 * Plugin URI: https://github.com/crstauf/query-monitor-extend/tree/master/heartbeat
 * Description: Query Monitor collector for heartbeats.
 * Version: 1.0
 * Author: Caleb Stauffer
 * Author URI: https://develop.calebstauffer.com
 * Update URI: false
 */

defined( 'WPINC' ) || die();

add_action( 'plugin_loaded', 'load_qmx_heartbeat_collector' );

function load_qmx_heartbeat_collector( string $file ) {

	if ( 'query-monitor/query-monitor.php' !== plugin_basename( $file ) )
		return;

	remove_action( 'plugin_loaded', __FUNCTION__ );

	if ( !class_exists( 'QueryMonitor' ) )
		return;

	if ( defined( 'QMX_DISABLE' ) && QMX_DISABLE )
		return;

	class QMX_Collector_Heartbeat extends QM_Collector {

		public $id = 'heartbeat';

		function __construct() {
			parent::__construct();

			if ( $this->qm_no_jquery() )
				return;

			add_action( 'wp_enqueue_scripts', array( &$this, 'add_inline_script' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'add_inline_script' ) );
		}

		public function add_inline_script() {
			wp_add_inline_script( 'heartbeat', $this->_inlineScript_heartbeat() );
		}

		public function _inlineScript_heartbeat() {
			ob_start();
			?>

			var qmx_heartbeat = {

				_beat: null,
				_beats: [],
				_start: 0,
				_table: null,
				_tab: null,
				_table_ready: false,

				_prev_dub: 0,

				init: function() {

					var that = this;
					var d = new Date();
					this._start = d.getTime();

					jQuery( document ).on( 'heartbeat-send', function() {
						var d = new Date();
						var lub = d.getTime();

						if ( !that._table_ready ) {
							that._beat = { lub: lub };
							return;
						}

						var count = ( that._beats.find( 'tr' ).length + 1 );

						if (
							2 == count
							&& that._beats.find( 'tr' ).hasClass( 'listening' )
						) {
							that._beats.html( '' );
							count = 1;
						}

						that.add_table_row( that.get_table_row(
							count,
							lub,
							'',
							(
								0 == that._prev_dub
								? '-'
								: ( lub - that._prev_dub )
							),
							'-'
						) );
						that.update_tab( count );
						that._beat = that._beats.find( 'tr:first-child' );
					} );

					jQuery( document ).on( 'heartbeat-tick', function() {
						var d = new Date();
						var dub = d.getTime();

						if ( !that._table_ready ) {
							that._beat.dub = dub;
							that['_beats'].push( that._beat );
							that._beat = null;
							return;
						}

						var dub_secs = ( dub - that._start ) / 1000;
						var duration = ( ( ( dub - that._start ) / 1000 ) - parseFloat( that._beat.find( 'td.lub' ).text() ) );

						that._beat.find( 'td.dub' ).html( dub_secs.toFixed( 3 ) );
						that._beat.find( 'td.dur' ).html( duration.toFixed( 3 ) );
						that._prev_dub = dub;
					} );

				},

				populate_table: function() {
					this._table_ready = true;
					this._table = jQuery( <?php echo json_encode( '#' . esc_attr( $this->id() ) . ' > table' ) ?> );
					this._beats = this._table.find( 'tbody' );
				},

				add_table_row: function( tr ) {
					this._table.find( 'tbody' ).prepend( tr );
				},

				get_table_row: function( index, lub, dub, since_last, duration ) {
					var lub_diff = ( lub - this._start ) / 1000;
					var dub_diff = '-';
					var duration_secs = '-';

					if ( '' !== dub ) {
						dub_diff = ( dub - this._start ) / 1000;
						dub_diff = dub_diff.toFixed( 3 );
					}

					if ( !isNaN( duration ) ) {
						duration_secs = duration / 1000;
						duration_secs = duration_secs.toFixed( 3 );
					}

					var since_last_secs = '-' != since_last ? since_last / 1000 : '-';
					since_last_secs = '-' != since_last_secs ? since_last_secs.toFixed( 3 ) : '-';

					return '<tr' +
						(
							1 == ( index % 2 )
							? ' class="qm-odd"'
							: ''
						) +
					'>' +
						'<td class="qm-num">' + index + '</td>' +
						'<td class="qm-num lub">' + lub_diff.toFixed( 3 ) + '</td>' +
						'<td class="qm-num dub">' + dub_diff + '</td>' +
						'<td class="qm-num since">' + since_last_secs + '</td>' +
						'<td class="qm-num dur">' + duration_secs + '</td>' +
					'</tr>';
				},

				update_tab: function( count ) {
					this._tab = jQuery( '#qm-panel-menu button[data-qm-href=<?php echo json_encode( '#' . esc_attr( $this->id() ) ) ?>]' );
					this._tab.html( 'Heartbeats (' + count + ')' );
				}

			};

			qmx_heartbeat.init();

			<?php
			return ob_get_clean();
		}

		public function name() {
			return __( 'Heartbeat', 'query-monitor-extend' );
		}

		public function process() {
		}

		public function qm_no_jquery() {
			return defined( 'QM_NO_JQUERY' ) && QM_NO_JQUERY;
		}

		public function get_concerned_filters() : array {
			return array(
				'heartbeat_received',
			);
		}

	}

	add_filter( 'qm/collectors', static function ( array $collectors ) : array {
		$collectors['heartbeat'] = new QMX_Collector_Heartbeat;
		return $collectors;
	} );

}