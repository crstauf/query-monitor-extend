<?php
/**
 * Container for data collectors.
 *
 * @package query-monitor-extend
 */

class QMX_Collectors extends QM_Collectors {

	private $items = array();

	public static function add( QM_Collector $collector ) {
		$collectors = self::init();
		QM_Collectors::add( $collector );
		$collectors->items[$collector->id] = QM_Collectors::get( $collector->id );
	}

	public static function init() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self;
		}

		return $instance;

	}

}