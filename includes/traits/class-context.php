<?php
/**
 * Context.
 *
 * @package HivePress\Traits
 */

namespace HivePress\Traits;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements object context.
 */
trait Context {

	/**
	 * Object context values.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Gets object context values.
	 *
	 * @param string $name Context name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	final public function get_context( $name = null, $default = null ) {
		return $name ? hp\get_array_value( $this->context, $name, $default ) : $this->context;
	}
}
