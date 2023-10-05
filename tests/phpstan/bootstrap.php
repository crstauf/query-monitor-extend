<?php

if ( ! function_exists( 'is_production' ) ) {

	/**
	 * Check if production environment.
	 *
	 * @return bool
	 */
	function is_production() : bool {
		return 'production' === wp_get_environment_type();
	}

}