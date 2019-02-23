<?php
/**
 * User login controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User login controller class.
 *
 * @class User_Login
 */
class User_Login extends Controller {

	/**
	 * Controller URL.
	 *
	 * @var string
	 */
	protected $url = '^account/login/?$';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->set_title( esc_html__( 'Sign In', 'hivepress' ) );
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
