<?php
/**
 * User account controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User account controller class.
 *
 * @class User_Account
 */
class User_Account extends Controller {

	/**
	 * Controller URL.
	 *
	 * @var string
	 */
	protected $url = '^account/?$';

	/**
	 * Renders controller response.
	 *
	 * @return string
	 */
	public function render() {
		return 'todo';
	}
}
