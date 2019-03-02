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
			call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
		}
	}

	/**
	 * Sets controller name.
	 *
	 * @param string $name Controller name.
	 */
	final protected function set_name( $name ) {
		$this->name = $name;
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
	 * Sets controller routes.
	 *
	 * @param array $routes Controller routes.
	 */
	final protected function set_routes( $routes ) {
		$this->routes = $routes;
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
