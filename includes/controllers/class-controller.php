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
			$this->set_property( $name, $value );
		}
	}

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final protected function set_property( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			if ( method_exists( $this, 'set_' . $name ) ) {
				$this->$name = call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
			} else {
				$this->$name = $value;
			}
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
