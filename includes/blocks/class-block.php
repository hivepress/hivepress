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

		// todo remove.
		unset( $args['type'] );
		unset( $args['order'] );

		// Set properties.
		foreach ( $args as $name => $value ) {
			call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
		}
	}

	/**
	 * Sets block title.
	 *
	 * @param string $title Block title.
	 */
	final protected function set_title( $title ) {
		$this->title = $title;
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
	 * Sets block attributes.
	 *
	 * @param array $attributes Block attributes.
	 */
	final protected function set_attributes( $attributes ) {
		$this->attributes = (array) $attributes;
	}

	/**
	 * Gets block attribute.
	 *
	 * @param mixed $name Attribute name.
	 */
	final protected function get_attribute( $name ) {
		return hp_get_array_value( $this->attributes, $name );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
