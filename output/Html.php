<?php
/**
 * Abstract output class for HTML pages.
 *
 * @package query-monitor-extend
 */

abstract class QMX_Output_Html extends QM_Output_Html {

	public function panel_menu( array $menu ) { return $menu; }

}