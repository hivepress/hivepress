<?php
/**
 * Abstract component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract component class.
 */
abstract class Component {
	use Traits\Mutator;

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps component properties.
	 */
	protected function boot() {}

	/**
	 * Sets the action and filter callbacks.
	 *
	 * @param array $callbacks Callback arguments.
	 */
	final protected function set_callbacks( $callbacks ) {
		foreach ( $callbacks as $callback ) {

			// Get hook type.
			$type = hp\get_array_value( $callback, 'filter' ) ? 'filter' : 'action';

			// Register callback.
			call_user_func_array(
				'add_' . $type,
				[
					$callback['hook'],
					$callback['action'],
					hp\get_array_value( $callback, '_order', 10 ),
					hp\get_array_value( $callback, 'args', 1 ),
				]
			);
		}
	}
}
