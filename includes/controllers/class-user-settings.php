<?php
/**
 * User settings controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User settings controller class.
 *
 * @class User_Settings
 */
class User_Settings extends Controller {

	/**
	 * Controller URL.
	 *
	 * @var string
	 */
	protected $url = '^account/settings/?$';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->set_title( esc_html__( 'My Settings', 'hivepress' ) );
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
