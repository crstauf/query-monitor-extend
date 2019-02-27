<?php
/**
 * Abstract data collector.
 *
 * @package query-monitor-extend
 */

abstract class QMX_Collector extends QM_Collector {

	protected static $hide_qmx = null;

	public static function hide_qmx() {
		if ( null === self::$hide_qmx ) {
			self::$hide_qmx = ( defined( 'QMX_HIDE_SELF' ) && QMX_HIDE_SELF );
		}

		return self::$hide_qmx;
	}

	public function filter_remove_qmx( array $item ) {
		$component = $item['trace']->get_component();
		return ( 'query-monitor-extend' !== $component->context );
	}

}