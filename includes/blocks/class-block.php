<?php
/**
 * Abstract block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract block class.
 *
 * @class Block
 */
abstract class Block {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Block attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {

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
				call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
			} else {
				$this->$name = $value;
			}
		}
	}

	/**
	 * Gets block title.
	 *
	 * @return string
	 */
	final public function get_title() {
		return $this->title;
	}

	/**
	 * Gets block attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		return $this->attributes;
	}

	/**
	 * Gets block attribute.
	 *
	 * @param string $name Attribute name.
	 * @return mixed
	 */
	final protected function get_attribute( $name ) {
		return hp_get_array_value( $this->get_attributes(), $name );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
