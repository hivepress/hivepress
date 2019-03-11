<?php
/**
 * Abstract controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract controller class.
 *
 * @class Controller
 */
abstract class Controller {
	use Traits\Mutator;

	/**
	 * Controller name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Controller routes.
	 *
	 * @var array
	 */
	protected $routes = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {

		// Set name.
		$args['name'] = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}
	}

	/**
	 * Gets controller name.
	 *
	 * @return string
	 */
	final public function get_name() {
		return $this->name;
	}

	/**
	 * Gets controller routes.
	 *
	 * @return array
	 */
	final public function get_routes() {
		return $this->routes;
	}
}
