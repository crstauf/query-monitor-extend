<?php
/**
 * Var dumps collector.
 *
 * @package query-monitor-extend
 */

class QMX_Collector_Var_Dumps extends QMX_Collector {

	public $id = 'var_dumps';

	function __construct() {
		add_action( 'qmx/var_dump', array( &$this, 'collect' ), 10, 2 );
		parent::__construct();
		$this->data['vars'] = array();
	}

	public function name() {
		return __( 'Var Dumps', 'query-monitor-extend' );
	}

	public function collect( $var, $label = null ) {
		if ( is_null( $label ) )
			$label = time();
		else if ( array_key_exists( $label, $this->data['vars'] ) )
			$label .= ' (' . time() . ')';

		$this->data['vars'][$label] = $var;
	}

}

QMX_Collectors::add( new QMX_Collector_Var_Dumps );

// Backwards compatibility
if ( !function_exists( 'qmx_dump' ) ) {

	function qmx_dump( $var, $label = null ) {
		if ( QMX_Collectors::get( 'var_dumps' ) )
			QMX_Collectors::get( 'var_dumps' )->collect( $var, $label );
	}

}

if ( !function_exists( 'qm_dump' ) ) {

	function qm_dump( $var, $label = null ) {
		qmx_dump( $var, $label );
	}

}