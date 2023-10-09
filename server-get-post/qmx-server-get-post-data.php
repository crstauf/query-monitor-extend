<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

class QMX_Data_Server_Get_Post extends QM_Data {

	/**
	 * @var array<string, scalar>
	 */
	public $server;

	/**
	 * @var array<string, scalar>
	 */
	public $get;

	/**
	 * @var array<string, scalar>
	 */
	public $post;

}