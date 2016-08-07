<?php

if (!defined('ABSPATH') || !function_exists('add_filter')) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

if ( class_exists( 'QM_Collector_Transients' ) )
	new QMX_Collector_Transients;

class QMX_Collector_Transients {

	private $qm_collector;
	private $new_transients = array();

	public function __construct() {
		$this->qm_collector = QM_Collectors::get( 'transients' );

		add_action( 'add_option',            array( $this, 'action_add_option' ) );
		add_action( 'setted_site_transient', array( $this, 'action_setted_site_transient' ), 10, 3 );
		add_action( 'setted_transient',      array( $this, 'action_setted_blog_transient' ), 10, 3 );
		add_action( 'update_site_option',    array( $this, 'action_update_site_option' ), 10, 3 );
		add_action( 'updated_option',        array( $this, 'action_update_blog_option' ), 10, 3 );
	}

	public function action_add_option( $option ) {
		if (
			false !== stripos( $option, '_transient_timeout_' )
			|| 0 !== stripos( $option, '_transient_' )
			|| false !== get_option( $option )
		)
			return;

		$transient = str_replace( '_transient_', '', $option );
		$this->new_transients[$transient] = 1;
	}

	public function action_setted_site_transient( $transient, $value = null, $expiration = null ) {
		$this->setted_transient( $transient, 'site', $value, $expiration );
	}

	public function action_setted_blog_transient( $transient, $value = null, $expiration = null ) {
		$this->setted_transient( $transient, 'blog', $value, $expiration );
	}

	public function setted_transient( $transient, $type, $value = null, $expiration = null ) {
		$trace = new QM_Backtrace;
		$this->qm_collector->additional_data['trans'][$transient] = array(
			'transient'  => $transient,
			'trace'      => $trace,
			'type'       => $type,
			'value'      => $value,
			'status'     => in_array( $transient, $this->new_transients ) ? 'added' : 'updated',
			'expiration' => $expiration,
		);

		if ( in_array( $transient, array_keys( $this->new_transients ) ) )
			unset( $this->new_transients[$transient] );
	}

	public function action_update_site_option( $option, $old_value, $new_value ) {
		$this->updated_option( $option, 'site', $new_value );
	}

	public function action_update_blog_option( $option, $old_value, $new_value ) {
		$this->updated_option( $option, 'blog', $new_value );
	}

	public function updated_option( $option, $type, $expiration ) {
		if ( 0 !== stripos( $option, '_transient_timeout_' ) )
			return;

		$transient = str_replace( '_transient_timeout_', '', $option );

		if ( isset( $this->qm_collector->additional_data['trans'][$transient] ) ) {
			$this->qm_collector->additional_data['trans'][$transient]['status'] = 'extended';
		} else {
			$trace = new QM_Backtrace;
			$this->qm_collector->additional_data['trans'][$transient] = array(
				'transient'  => $transient,
				'trace'      => $trace,
				'type'       => $type,
				'status'     => 'extended',
				'expiration' => $expiration - time(),
			);
		}
	}

}
