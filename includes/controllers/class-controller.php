<?php
/**
 * Abstract controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract controller class.
 *
 * @class Controller
 */
abstract class Controller {

	/**
	 * Class constructor.
	 */
	public function __construct() {

	}

	/**
	 * Matches controller URL.
	 *
	 * @return bool
	 */
	abstract public function match();

	/**
	 * Renders controller response.
	 *
	 * @return string
	 */
	abstract public function render();
}
