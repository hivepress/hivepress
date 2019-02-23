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
	private $name;

	/**
	 * Controller title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Controller URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Set name.
		$this->name = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 */
	final public function __call( $name, $args ) {
		$prefixes = array_filter(
			[
				'set',
				'get',
			],
			function( $prefix ) use ( $name ) {
				return strpos( $name, $prefix . '_' ) === 0;
			}
		);

		if ( ! empty( $prefixes ) ) {
			$method = reset( $prefixes );
			$arg    = substr( $name, strlen( $method ) + 1 );

			return call_user_func_array( [ $this, $method ], array_merge( [ $arg ], $args ) );
		}
	}

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final private function set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->$name = $value;
		}
	}

	/**
	 * Gets property.
	 *
	 * @param string $name Property name.
	 */
	final private function get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->$name;
		}
	}

	// Forbid setting name.
	final private function set_name() {}

	/**
	 * Matches controller URL.
	 *
	 * @return bool
	 */
	public function match() {
		return get_query_var( 'hp_controller' ) === $this->get_name();
	}

	/**
	 * Renders controller response.
	 *
	 * @return string
	 */
	abstract public function render();
}
