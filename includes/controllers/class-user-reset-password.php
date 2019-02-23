<?php
/**
 * User reset password controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User reset password controller class.
 *
 * @class User_Reset_Password
 */
class User_Reset_Password extends Controller {

	/**
	 * Controller URL.
	 *
	 * @var string
	 */
	protected $url = '^account/reset-password/?$';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->set_title( esc_html__( 'Reset Password', 'hivepress' ) );
	}

	/**
	 * Renders controller response.
	 *
	 * @return string
	 */
	public function render() {
		return 'todo';
	}
}
