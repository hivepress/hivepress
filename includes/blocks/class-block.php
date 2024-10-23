<?php
/**
 * Abstract block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract block class.
 */
abstract class Block {
	use Traits\Mutator;
	use Traits\Context;

	use Traits\Meta {
		set_meta as _set_meta;
	}

	/**
	 * Block arguments.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name'     => hp\get_class_name( static::class ),
				'settings' => [],
			],
			$meta
		);

		// Filter meta.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the block class meta. The class meta stores properties related to the block type rather than a specific block instance. For example, it stores the block settings displayed in the editor. The dynamic part of the hook refers to the block type (e.g. `listings`). You can check the available block types in the `includes/blocks` directory of HivePress.
			 *
			 * @hook hivepress/v1/blocks/{block_type}/meta
			 * @param {array} $meta Class meta values.
			 * @return {array} Class meta values.
			 */
			$meta = apply_filters( 'hivepress/v1/blocks/' . hp\get_class_name( $class ) . '/meta', $meta );
		}

		// Set meta.
		static::set_meta( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the block properties. The dynamic part of the hook refers to the block type (e.g. `listings`). You can check the available block types in the `includes/blocks` directory of HivePress.
			 *
			 * @hook hivepress/v1/blocks/{block_type}
			 * @param {array} $props Block properties.
			 * @param {object} $block Block object.
			 * @return {array} Block properties.
			 */
			$args = apply_filters( 'hivepress/v1/blocks/' . hp\get_class_name( $class ), $args, $this );
		}

		// Set arguments.
		$this->args = $args;

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {}

	/**
	 * Sets class meta values.
	 *
	 * @param array $meta Meta values.
	 */
	final protected static function set_meta( $meta ) {

		// Set settings.
		$settings = array_filter( hp\get_array_value( $meta, 'settings', [] ) );

		if ( $settings ) {
			$meta['settings'] = [];

			foreach ( hp\sort_array( $settings ) as $name => $args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

				// Add field.
				if ( $field ) {
					$meta['settings'][ $name ] = $field;
				}
			}
		}

		static::_set_meta( $meta );
	}

	/**
	 * Sets object context value.
	 *
	 * @param string $name Context name.
	 * @param mixed  $value Context value.
	 */
	final protected function set_context( $name, $value = null ) {
		if ( is_array( $name ) ) {
			$this->context = $name;

			// @todo remove when optimized globally.
			unset( $this->args['context'] );
		} else {
			$this->context[ $name ] = $value;
		}
	}

	/**
	 * Get block arguments.
	 *
	 * @return array
	 */
	final public function get_args() {
		return $this->args;
	}

	/**
	 * Gets block argument.
	 *
	 * @param string $name Argument name.
	 * @return mixed
	 */
	final public function get_arg( $name ) {
		return hp\get_array_value( $this->args, $name );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
