<?php
/**
 * Heartbeat collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_Heartbeat extends QMX_Collector {

	public $id = 'heartbeat';

	function __construct() {
		parent::__construct();

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

					that.add_table_row( that.get_table_row( ( that._beats.find( 'tr' ).length + 1 ), lub, '', ( 0 == that._prev_dub ? '-' : ( lub - that._prev_dub ) ), '-' ) );
					that._beat = that._beats.find( 'tr:last-child' );
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

					that._beat.find( 'td.dub' ).html( dub - that._start );
					that._beat.find( 'td.dur' ).html( ( dub - that._start ) - parseFloat( that._beat.find( 'td.lub' ).html() ) );
					that._prev_dub = dub;
				} );

			},

			populate_table: function() {
				this._table_ready = true;
				this._table = jQuery( <?php echo json_encode( '#' . esc_attr( $this->id() ) . ' > table' ) ?> );
				this._beats = this._table.find( 'tbody' );
			},

			add_table_row: function( tr ) {
				this._table.find( 'tbody:last-child' ).append( tr );
			},

			get_table_row: function( index, lub, dub, since_last, duration ) {
				return '<tr>' +
					'<td class="qm-num">' + index + '</td>' +
					'<td class="qm-num lub">' + ( lub - this._start ) + '</td>' +
					'<td class="qm-num dub">' + ( dub - this._start ) + '</td>' +
					'<td class="qm-num since">' + since_last + '</td>' +
					'<td class="qm-num dur">' + duration + '</td>' +
				'</tr>';
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

}

function register_qmx_collector_heartbeat( array $collectors, QueryMonitorExtend $qmx ) {
	$collectors['heartbeat'] = new QMX_Collector_Heartbeat;
	return $collectors;
}

add_filter( 'qmx/collectors', 'register_qmx_collector_heartbeat', 10, 2 );